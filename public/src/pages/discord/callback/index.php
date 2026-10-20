<?php
// Handler /discord/callback - intercepté tôt dans public/index.php (avant le template)
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/loadEnv.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/PDO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/csrf.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/logging.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/rate_limiter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/discord_oauth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/user_merge.php';

checkRateLimitByIP($pdo, 'discord_callback', 10, 15);

// 1) Valider le state AVANT tout échange de code
if (!discordValidateState()) {
    logSecurityEvent('discord_state_invalid', []);
    echo "<p>Erreur de sécurité : state OAuth invalide</p>";
    exit;
}

// 2) Refus de l'utilisateur : Discord redirige ?error=access_denied&error_description=...
//    SANS code, mais AVEC le state (la validation 1) passe donc). On repart proprement
//    sur /discord/login au lieu d'afficher « Réponse Discord invalide »
if (isset($_GET['error']) && is_string($_GET['error']) && !empty($_GET['error'])) {
    $errorDescription = "";
    if (isset($_GET['error_description']) && is_string($_GET['error_description'])) {
        $errorDescription = $_GET['error_description'];
    }
    logSecurityEvent('discord_user_denied', ["error" => $_GET["error"], "description" => $errorDescription]);
    header("location: /discord/login");
    echo "<script>window.location.href = '/discord/login';</script>";
    exit;
}

// 3) Vérifier le code (is_string avant trim() : évite le TypeError si ?code[]=x)
if (!isset($_GET['code']) || !is_string($_GET['code']) || empty(trim($_GET['code']))) {
    logSecurityEvent('discord_callback_no_code', []);
    echo "<p>Réponse Discord invalide</p>";
    exit;
}

// 4) Échange code -> token, puis profil Discord
$token = discordExchangeCode(trim($_GET['code']));
if ($token === null) {
    logSecurityEvent('discord_token_exchange_failed', []);
    echo "<p>Impossible de contacter Discord</p>";
    exit;
}

$discordUser = discordFetchUser($token); // -> id, username, avatar, ...
if ($discordUser === null || empty($discordUser['id'])) {
    logSecurityEvent('discord_user_fetch_failed', []);
    echo "<p>Impossible de récupérer le profil Discord</p>";
    exit;
}

$discordId = $discordUser['id'];
$discordUsernameSaved = "";
if (isset($discordUser['username']) && is_string($discordUser['username'])) {
    $discordUsernameSaved = $discordUser['username'];
}


$alreadyLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && !empty($_SESSION['id']);

if ($alreadyLoggedIn === true) {
    // Un compte ne peut pas remplacer un Discord déjà lié par un autre Discord.
    // Le même Discord reste autorisé (connexion idempotente).
    $currentUser = discordFindUserById($pdo, (int)$_SESSION['id']);
    if ($currentUser === null) {
        logSecurityEvent('discord_link_user_missing', ['user_id' => $_SESSION['id']]);
        echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard</p>";
        exit;
    }

    $currentDiscordId = "";
    if (!empty($currentUser["discord_id"])) {
        $currentDiscordId = (string)$currentUser["discord_id"];
    }

    if ($currentDiscordId !== "" && $currentDiscordId !== (string)$discordId) {
        logSecurityEvent('discord_link_conflict_already_linked', [
            'user_id' => $_SESSION['id'],
            'existing_discord_id' => $currentDiscordId,
        ]);
        logSecurityEvent('discord_link_account_already_linked', ['user_id' => $_SESSION['id']]);
        echo "<p>Ce compte est déjà lié à un autre compte Discord</p>";
        exit;
    }
}

// Avatar Discord : hash (ou vide = avatar par défaut) -> téléchargé en local
$discordAvatarHash = "";
if (isset($discordUser['avatar']) && is_string($discordUser['avatar'])) {
    $discordAvatarHash = $discordUser['avatar'];
}
discordDownloadAvatar($discordId, $discordAvatarHash);

if ($alreadyLoggedIn === true) {
    // CAS LIAISON : le compte classique est déjà connecté
    // Le propriétaire actuel du discord_id
    $owner = discordFindUserByDiscordId($pdo, $discordId);
    if ($owner !== null && (int)$owner['id'] !== (int)$_SESSION['id']) {
        if (!empty($owner['username'])) {
            // Vrai conflit : ce Discord est déjà lié à un AUTRE compte classique
            logSecurityEvent('discord_link_conflict', ['user_id' => $_SESSION['id']]);
            echo "<p>Ce compte Discord est déjà lié à un autre compte</p>";
            exit;
        }

        // La ligne BDD du compte Discord-only est fusionnée : toutes ses données sont réaffectées vers le compte courant avant sa suppression.
        try {
            mergeUserAccounts($pdo, (int)$owner['id'], (int)$_SESSION['id'], (string)$discordId);
            logSecurityEvent('discord_link_ghost_merged', ["user_id" => $_SESSION["id"], "ghost_id" => (int)$owner["id"]]);
        }
        catch(PDOException $e) {
            if ($e->getCode() === "23000") {
                logSecurityEvent('discord_link_conflict', ['user_id' => $_SESSION['id']]);
                echo "<p>Ce compte Discord est déjà lié à un autre compte</p>";
                exit;
            }
            else {
                logSecurityEvent('discord_link_merge_error', ['user_id' => $_SESSION['id'], 'ghost_id' => (int)$owner['id']]);
                echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard</p>";
                exit;
            }
        }
        catch(Throwable $e) {
            logSecurityEvent('discord_link_merge_error', ['user_id' => $_SESSION['id'], 'ghost_id' => (int)$owner['id']]);
            echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard</p>";
            exit;
        }
    }
    else {
        // Même Discord déjà lié, ou Discord libre : la condition SQL empêche tout remplacement d'un autre Discord.
        try {
            $affectedRows = discordLinkToUser($pdo, (int)$_SESSION["id"], $discordId);

            // rowCount vaut 0 si le même Discord était déjà lié (aucun changement).
            // En revanche, si le compte a été lié à un autre Discord entre-temps,
            // la garde SQL n'a rien modifié : c'est un conflit.
            if ($affectedRows !== 1 && $currentDiscordId !== (string)$discordId) {
                logSecurityEvent('discord_link_conflict', ['user_id' => $_SESSION['id']]);
                echo "<p>Ce compte Discord est déjà lié à un autre compte</p>";
                exit;
            }
        }
        catch(PDOException $e) {
            if ($e->getCode() === "23000") {
                // Course/Race condition : le discord_id a été pris entre le SELECT et l'UPDATE
                logSecurityEvent('discord_link_conflict', ['user_id' => $_SESSION['id']]);
                echo "<p>Ce compte Discord est déjà lié à un autre compte</p>";
                exit;
            }
            else {
                logSecurityEvent('discord_link_error', ['user_id' => $_SESSION['id']]);
                echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard</p>";
                exit;
            }
        }
    }

    $_SESSION['discord_id'] = $discordId;
    $_SESSION['discord_username'] = $discordUsernameSaved;
    // $_SESSION['username'] (nom classique) reste inchangé, de même que is_admin,
    // sauf si ce discord_id est celui configuré admin (ADMIN_DISCORD_USER_ID)
    if (discordIsAdminId($discordId)) {
        $_SESSION["is_admin"] = true;
    }
    logSecurityEvent('discord_link_success', ['user_id' => $_SESSION['id']]);
}
else {
    // CAS CONNEXION via Discord
    $dbUser = discordFindUserByDiscordId($pdo, $discordId);
    $userId = 0;
    $classicUsername = "";
    if ($dbUser === null) {
        // TOCTOU : un autre callback peut créer la ligne entre le SELECT et l'INSERT
        try {
            // Aucune ligne avec ce discord_id -> créer une ligne Discord-only
            $userId = discordCreateUser($pdo, $discordId); // username/password NULL en BDD
        }
        catch(PDOException $e) {
            if ($e->getCode() === "23000") {
                // Course d'INSERT : la ligne existe maintenant -> re-sélectionner et
                // continuer le flow « ligne existante » (le login réussira)
                $dbUser = discordFindUserByDiscordId($pdo, $discordId);
                if ($dbUser !== null) {
                    logSecurityEvent('discord_create_race', ["discord_id" => $discordId]);
                }
                else {
                    // Toujours rien : vraie erreur d'écriture
                    logSecurityEvent('discord_create_error', ["discord_id" => $discordId]);
                    echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard</p>";
                    exit;
                }
            }
            else {
                logSecurityEvent('discord_create_error', ["discord_id" => $discordId]);
                echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard</p>";
                exit;
            }
        }
    }
    if ($dbUser !== null) {
        // Ligne existante -> réutiliser son id
        $userId = (int)$dbUser['id'];
        if (!empty($dbUser['username'])) {
            $classicUsername = $dbUser['username'];    // compte classique lié
        }
        else {
            $classicUsername = "";                     // compte Discord-only
        }
    }

    session_regenerate_id(true); // évite la fixation de session
    $_SESSION['loggedin'] = true;
    $_SESSION['id'] = $userId;
    $_SESSION['username'] = $classicUsername;                  // nom classique, vide si Discord-only
    $_SESSION['discord_id'] = $discordId;                       // id Discord
    $_SESSION['discord_username'] = $discordUsernameSaved;      // nom Discord (jamais fusionné avec username)

    // même logique is_admin que login.php (comparaison ADMIN_USERNAME, seulement si username non vide)
    $_SESSION["is_admin"] = false;
    if ($classicUsername !== "") {
        $adminUsername = getenv("ADMIN_USERNAME");
        if (empty($adminUsername) || !isset($adminUsername)) {
            $adminUsername = "administrator";
        }
        if (strcasecmp($classicUsername, $adminUsername) === 0) {
            $_SESSION["is_admin"] = true;
        }
    }

    // Admin Discord : le discord_id peut être configuré au .env (ADMIN_DISCORD_USER_ID)
    if (discordIsAdminId($discordId)) {
        $_SESSION["is_admin"] = true;
    }

    logSecurityEvent('discord_login_success', ['user_id' => $userId]);
}

header("location: /");
echo "<script>window.location.href = '/';</script>";
?>

# Discord Auth
## Description
- En PHP, ajouter un
> se connecter avec discord
- L'utilisateur peut soit se connecter avec un nom de compte et mot de passe comme avant
  - Ou connecter son compte discord
  - Ou Lier automatiquement son compte discord à son compte classique si il est déjà connecté
  - Ou créer un compte classique et la rattacher à un compte Discord déjà existant sur la base de donnée

### Scenario
| Scenario | `username` | `password` | `discord_id` |
|---|---|---|---|
| Password-only | set | set | NULL |
| Discord-only | NULL | NULL | set |
| Both (linked) | set | set | set |
- `username` et `password` doivent donc être **nullable** (actuellement `NOT NULL` dans `create-db.php`)
- Le `UNIQUE` sur `username` ET sur `discord_id` garantit l'absence de doublon
  - MariaDB autorise plusieurs `NULL` dans un index `UNIQUE`

## Décisions d'architecture (à respecter)
- **Routage** : le site route `/<uri>` vers `public/src/pages/<uri>/index.php` (+ `head.php`), voir `public/index.php`
  - Ne PAS créer de `.php` à la racine du domaine (un fichier à la racine n'est pas routé)
  - Callback : handler `public/src/pages/discord/callback/index.php`, intercepté tôt dans `index.php`
    → URL `/discord/callback` (pas de `head.php` : on sort avant le template)
  - Inscription Discord : `public/src/pages/discord/register/index.php` + `head.php` → URL `/discord/register`
  - `redirect_uri` doit correspondre EXACTEMENT à l'URL déclarée sur le portail développeur Discord
- **Secrets** : dans `.env` (jamais en dur dans le code)
  - `DISCORD_CLIENT_ID`, `DISCORD_CLIENT_SECRET`, `DISCORD_REDIRECT_URI`
- **Client_id existant** : `1186411072077447218` (déjà utilisé dans `/discord/login/login.html`)
  - L'ancien flow `response_type=token` (implicit grant) est déprécié → à remplacer par authorization code
- **SameSite** : `public/index.php` force actuellement `SameSite=Strict` pour tout le site
  - `Strict` empêche le cookie de session d'être envoyé au retour cross-site depuis Discord
  - → session vide : `$_SESSION['oauth_state']` absent ET `$_SESSION['loggedin']` perdu
  - Conséquences : le login Discord échoue (state), et le cas « lier » (compte classique déjà connecté)
    est confondu avec un login anonyme
  - **Décision : `Lax` uniquement sur les routes Discord, `Strict` partout ailleurs**
    - Dans `index.php`, AVANT `session_start()`, choisir `Lax` si le chemin commence par
      `/discord/login`, `/discord/callback` ou `/discord/register`
    - `php.ini` seul ne suffit PAS : `index.php` écrase la config via `session_set_cookie_params()`
  - `Lax` reste sûr : pas d'envoi sur les POST cross-site, et `state` + token CSRF protègent déjà
  - **Mise au point** : `SameSite` ne change PAS le domaine destinataire du cookie
    - Le cookie n'est envoyé qu'à `doc.altherneum.fr`, jamais à `discord.com`
    - Au retour Discord, c'est le NAVIGATEUR de l'utilisateur qui appelle notre callback (pas le serveur Discord)
    - Discord ne voit donc jamais notre cookie ; seul le `code` (jetable) et le `state` (usage unique) transitent
    - `$_SESSION['id']` n'est pas dans le cookie : il ne contient qu'un `PHPSESSID` opaque (HttpOnly + Secure)
- **Callback hors template** : `/discord/callback` est intercepté tôt dans `public/index.php`
  (comme la route `health`), avant le HTML, puis `exit`
  - Évite d'afficher header/footer autour d'une simple redirection
- **Ancien flow supprimé** (19/10/2026) : `/discord/tools` (implicit grant), son dossier et son lien dans `login.html` ont été retirés ; seul le flow authorization code subsiste

# Tâches
## 1. Modifier la base de donnée (`create-db.php`)
- Rendre `username` et `password` NULLables
- Ajouter `discord_id BIGINT UNSIGNED NULL UNIQUE` et `discord_linked_at DATETIME NULL`
```php
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NULL UNIQUE,
    password VARCHAR(255) NULL,
    discord_id BIGINT UNSIGNED NULL UNIQUE,
    discord_linked_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";
```
- Pour le moment on ne fait PAS de script de migration : la base est détruite et recréée à chaque test
  - Donc on se contente de modifier le `CREATE TABLE` ci-dessus
  - (`CREATE TABLE IF NOT EXISTS` n'altère pas une base existante, mais ce n'est pas un souci ici)

## 2. Ajouter `public/src/php/discord_oauth.php`
- Fichier partagé (règle AGENTS : fonctionnalité réutilisée → `./src/php/`)
- Contient la sécurité + les appels HTTP + les accès BDD
```php
// Construit l'URL d'autorisation et stocke le state en session
function discordBuildAuthorizeUrl(): string
{
    if (!discordIsConfigured()) {
        return '';
    }
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth_state'] = $state;
    return "https://discord.com/api/oauth2/authorize"
        . "?client_id=" . urlencode(getenv('DISCORD_CLIENT_ID'))
        . "&redirect_uri=" . urlencode(getenv('DISCORD_REDIRECT_URI'))
        . "&response_type=code"
        . "&scope=identify"
        . "&state=" . $state;
}

// Valide et consomme le state (une seule fois)
function discordValidateState(): bool
{
    if (!isset($_GET['state']) || !is_string($_GET['state']) || empty($_SESSION['oauth_state'])) {
        return false;
    }
    if (!hash_equals($_SESSION['oauth_state'], $_GET['state'])) {
        return false;
    }
    unset($_SESSION['oauth_state']); // usage unique
    return true;
}
```
- ⚠️ **`is_string()` OBLIGATOIRE avant `hash_equals()`** : sans lui, une requête
  `?state[]=x` fait exploser `hash_equals()` en `TypeError` PHP 8 (HTTP 500 au lieu
  d'une erreur propre)
- Même garde `is_string()` dans le callback (§4) : `$_GET['code']` avant `trim()`,
  et `$_GET['error']` / `$_GET['error_description']` avant lecture
- `discordExchangeCode($code)` : POST cURL sur `/api/oauth2/token`
  - cURL (extension déjà activée dans `php.ini`), timeouts, `Accept: application/json`
  - Retourner `null` si pas de `access_token`
- `discordFetchUser($token)` : GET `/api/users/@me` avec `Authorization: Bearer <token>`
  - Retourner un tableau ou `null`
- Helpers BDD : `discordFindUserByDiscordId()`, `discordCreateUser()`, `discordLinkToUser()`, `discordDeleteUser()`
  - `discordFindUserByDiscordId()` : retourne la ligne complète (`id`, `username`, `discord_id`, ...) ou `null`
    (sert à la fois au login Discord ET à la détection de la ligne "fantôme", voir section 4)
  - `discordCreateUser()` : INSERT avec `discord_id` et `discord_linked_at = NOW()` (username/password NULL)
  - `discordLinkToUser()` : UPDATE `discord_id` + `discord_linked_at = NOW()` WHERE `id = :id`

## 3. Bouton « Se connecter / Lier avec Discord »
- Générer l'URL via `discordBuildAuthorizeUrl()` (jamais en dur), le `state` est stocké en session au rendu
```
<a href="<?php echo discordBuildAuthorizeUrl(); ?>">Se connecter avec Discord</a>
```
- Le bouton doit couvrir les DEUX cas, sur `public/src/pages/discord/login/` :
  - Non connecté → « Se connecter avec Discord »
  - Connecté (compte classique) → « Lier mon compte Discord »
  - Libellé choisi selon `$_SESSION['loggedin']`
- ⚠️ Ne PAS mettre ce bouton sur `/login` : `login/index.php` redirige vers `/` si déjà connecté,
  donc un compte classique ne pourrait jamais lier son Discord
- ⚠️ Pièges d'emplacement (vérifiés dans le code) :
  - `/discord` est une redirection vers l'invitation du serveur (`discord.html`) → inutilisable
  - `/profile` n'existe pas (seulement `/profile/picture`)
  - `navlink.php` est commenté dans `header.php` → aucun menu pour atteindre la page
  - Le lien « Connectez-vous » de `index/index.php` couvre les non-connectés
  - → `/discord/login` est le point d'entrée principal (accessible par URL directe)
- ⚠️ **Le `state` ne doit être généré QUE depuis une route `/discord/*`**
  - PHP ré-émet le cookie `Set-Cookie: PHPSESSID=...; SameSite=...` à CHAQUE
    `session_start()` (vérifié par test) : le cookie porte l'attribut de la
    DERNIÈRE route visitée
  - Si le `state` est généré depuis une autre page (`/`, `/login`, ...), le cookie
    repasse en `Strict` et n'est PAS envoyé au retour cross-site depuis Discord
    → session vide → `oauth_state` perdu → échec
  - À respecter pour toute future évolution : pas de bouton Discord sur d'autres
    routes sans que l'utilisateur passe d'abord par une route `/discord/*`
- **Ancien lien supprimé** (19/10/2026) : l'implicit grant de `login.html` a été retiré avec la page (le fichier n'existe plus)

## 4. Callback `/discord/callback` (le bloc TODO corrigé)
- Handler : `public/src/pages/discord/callback/index.php`
- Intercepté tôt dans `public/index.php` (avant le template), puis `exit`
  - Ex : `if ($uri === 'discord/callback') { require $_SERVER['DOCUMENT_ROOT'] . '/src/pages/discord/callback/index.php'; exit; }`
- Ordre impératif : state → refus utilisateur (`error`) → code, et priorité au cas « déjà connecté »
```php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/loadEnv.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/PDO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/csrf.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/logging.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/rate_limiter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/discord_oauth.php';

checkRateLimitByIP($pdo, 'discord_callback', 10, 15);

// 1) Valider le state AVANT tout échange de code
if (!discordValidateState()) {
    logSecurityEvent('discord_state_invalid', []);
    echo "<p>Erreur de sécurité : state OAuth invalide</p>";
    exit;
}

// 2) Refus de l'utilisateur : Discord redirige ?error=access_denied&error_description=...
//    SANS code, mais AVEC le state (la validation 1) passe donc). Si l'utilisateur
//    a simplement refusé, on repart proprement sur /discord/login au lieu d'afficher
//    « Réponse Discord invalide »
//    (si Discord omet le state, on tombera sur le message « state OAuth invalide » : acceptable)
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

// 3) Vérifier le code (is_string avant trim() : sans lui, ?code[]=x ferait
//    exploser trim() en TypeError PHP 8 -> 500 au lieu d'une erreur propre)
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
$discordId = $discordUser['id']; // <- ne pas oublier de définir $discordId

$alreadyLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && !empty($_SESSION['id']);

if ($alreadyLoggedIn === true) {
    // CAS LIAISON : un compte classique est déjà connecté
    // Le propriétaire actuel du discord_id
    $owner = discordFindUserByDiscordId($pdo, $discordId);
    if ($owner !== null && (int)$owner['id'] !== (int)$_SESSION['id']) {
        if (!empty($owner['username'])) {
            // Vrai conflit : ce Discord est déjà lié à un AUTRE compte classique
            logSecurityEvent('discord_link_conflict', ['user_id' => $_SESSION['id']]);
            echo "<p>Ce compte Discord est déjà lié à un autre compte</p>";
            exit;
        }
        // Sinon c'est une ligne "fantôme" Discord-only (username NULL) :
        // on la supprime pour rattacher le discord_id au compte classique courant
        // ⚠️ Les FK users -> user_pictures / user_*_views sont ON DELETE CASCADE
        //    (vérifié dans create-db.php) : les données liées à la ligne fantôme
        //    (vues, photo de profil) sont supprimées en cascade avec elle
        discordDeleteUser($pdo, (int)$owner['id']);
        // Logger l'id supprimé (ghost_id) : indispensable pour le debug des cascades
        logSecurityEvent('discord_link_ghost_merged', ["user_id" => $_SESSION["id"], "ghost_id" => (int)$owner["id"]]);
    }
    // ⚠️ TOCTOU : entre le SELECT du propriétaire et cet UPDATE, un autre utilisateur
    // peut prendre le même discord_id -> condition UNIQUE violée au moment de l'écriture
    try {
        discordLinkToUser($pdo, (int)$_SESSION["id"], $discordId); // UPDATE ... WHERE id = :id
    }
    catch(PDOException $e) {
        if ($e->getCode() === "23000") {
            // Course : le discord_id a été pris entre le SELECT et l'UPDATE
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
    $_SESSION['discord_id'] = $discordId;
    $_SESSION['discord_username'] = $discordUser['username'];
    // $_SESSION['username'] (nom classique) reste inchangé
    logSecurityEvent('discord_link_success', ['user_id' => $_SESSION['id']]);
}
else {
    // CAS CONNEXION via Discord
    $dbUser = discordFindUserByDiscordId($pdo, $discordId);
    $userId = 0;
    $classicUsername = "";
    if ($dbUser === null) {
        // ⚠️ TOCTOU : entre ce SELECT et l'INSERT, un autre callback peut créer la
        // ligne (ex : deux onglets) -> UNIQUE(discord_id) violée au moment de l'INSERT
        try {
            // Aucune ligne avec ce discord_id -> créer une ligne Discord-only
            $userId = discordCreateUser($pdo, $discordId); // username/password NULL en BDD
        }
        catch(PDOException $e) {
            if ($e->getCode() === "23000") {
                // Course d'INSERT : la ligne existe maintenant -> on la re-sélectionne
                // et on continue le flow « ligne existante » (le login réussira)
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
    $_SESSION['username'] = $classicUsername;                  // nom classique, vide si pas de compte classique
    $_SESSION['discord_id'] = $discordId;                      // id Discord
    $_SESSION['discord_username'] = $discordUser['username'];  // nom Discord (jamais fusionné avec username)
    // même logique is_admin que login.php (comparaison ADMIN_USERNAME, seulement si username non vide)
    logSecurityEvent('discord_login_success', ['user_id' => $userId]);
}

header("location: /");
echo "<script>window.location.href = '/';</script>";
```

### Conflits d'unicité (TOCTOU)
- Le SELECT (`discordFindUserByDiscordId()`) puis l'INSERT/UPDATE ne sont **pas atomiques** :
  entre les deux, un autre callback (ex : deux onglets ouverts) peut modifier la table
- Conséquence : l'unicité explose au moment de l'**écriture** (`PDOException` 23000 / 1062
  duplicate), pas au moment du SELECT → chaque écriture est entourée d'un `try/catch`
  dans le pseudo-code ci-dessus :
  - `discordCreateUser()` (INSERT) : 23000 → **re-sélectionner** la ligne puis continuer
    le flow « ligne existante » (le login Discord réussit normalement) ;
    erreur générique seulement si la ligne est toujours absente
  - `discordLinkToUser()` (UPDATE `discord_id`) : 23000 → message conflit
    « Ce compte Discord est déjà lié à un autre compte »

### Suppression de la ligne fantôme (CASCADE)
- `discordDeleteUser()` supprime la ligne `users` : les FK `user_pictures`, `user_page_views`,
  `user_total_views`, `user_api_total_views`, `user_api_page_views` sont `ON DELETE CASCADE`
  (vérifié dans `create-db.php`) → les données liées (vues, photo de profil) de la ligne
  fantôme sont effacées en cascade avec elle
- Logger l'`id` supprimé (`ghost_id`) dans `discord_link_ghost_merged` : indispensable
  pour tracer quelle ligne et quelles données cascades ont disparu (debug)

### Variables de session
| Variable | Contenu |
|---|---|
| `$_SESSION['loggedin']` | `true` |
| `$_SESSION['id']` | id de la ligne `users` |
| `$_SESSION['username']` | nom **classique** (vide si pas de compte classique / Discord-only) |
| `$_SESSION['discord_id']` | id Discord |
| `$_SESSION['discord_username']` | nom Discord (toujours renseigné après un login Discord) |

- ⚠️ `$_SESSION['username']` devient une chaîne vide pour un compte Discord-only
  - Les tests actuels `isset($_SESSION['username'])` sont vrais avec une chaîne vide
    → les passer en `!empty($_SESSION['username'])`
  - Endroits exacts côté serveur (grep `isset($_SESSION['username'])` sur tout `src/`) :
    - `public/index.php` L25 : la limite rate-limit global passe de 20 (anonyme)
      à 50 req/min dès qu'un `username` est présent → un compte Discord-only passerait
      à 50 req/min sans bénéfice réel
      - ➜ **y penser** : passer le test en `!empty(...)` (mineur mais à faire)
    - `public/index.php` L96-97 : `logSecurityEvent('visit', ...)` loggerait un `username` vide
      - ➜ logger les **DEUX noms possibles** : `username` (nom classique, si non vide)
        ET `discord_username` (nom Discord, si non vide)
    - `src/index/index.php` L2-14 : le bloc « Bienvenue » afficherait un `<h1>` vide
      + la photo de profil
      - ➜ **il est prévu dans le PHP de cette page** de vérifier et d'afficher les deux noms
        si existants (nom classique et/ou nom Discord)
  - Sinon la page d'accueil afficherait un nom vide pour un compte Discord-only

## 5. `/discord/register` (créer un compte classique depuis un compte Discord)
- Page dédiée `public/src/pages/discord/register/index.php` + `head.php`
  (NE PAS réutiliser `/register`, car `register/index.php` redirige les utilisateurs `loggedin === true`,
  ce qu'est le compte Discord-only)
- Gardes d'accès :
  - Si `$_SESSION['discord_id']` non défini → redirect `/`
  - Si `$_SESSION['username']` non vide (compte classique déjà créé) → redirect `/`
- Réutiliser la validation de `register.php` : regex username, 6-24 chars, admin réservé, unicité
- **PAS de code d'invitation** : le `REGISTER_CODE` n'a pas d'utilité ici (l'utilisateur est déjà authentifié par Discord)
- Réutiliser `checkPassword()` (force du mot de passe)
- CSRF obligatoire (comme `login.html` / `register.html`)
- Ne PAS insérer une nouvelle ligne : **UPDATE** de la ligne Discord-only existante (l'`id` de session)
```sql
UPDATE users SET username = :username, password = :password
WHERE id = :id AND discord_id IS NOT NULL AND username IS NULL
```
- ⚠️ TOCTOU sur l'unicité du `username` : le SELECT d'unicité (copié de `register.php`)
  puis cet UPDATE ne sont **pas atomiques** → un autre utilisateur peut prendre le
  même `username` entre les deux
  - L'UPDATE explose alors sur la condition `username UNIQUE` (`PDOException` 23000/1062)
    même si le SELECT était bon
- ⚠️ L'UPDATE doit être entouré d'un `try/catch PDOException` :
  - 23000/1062 → même message que `register.php` : « Ce nom d'utilisateur est déjà pris »
  - autre → « Oups! Une erreur s'est produite. Veuillez réessayer plus tard »
- ⚠️ Vérifier `rowCount() === 1` après l'`UPDATE`
  - Sinon : erreur (la ligne a déjà un username, ou l'`id` de session est invalide)
- Puis mettre à jour la session : `$_SESSION['username'] = $username;` (le nom classique désormais défini) et `is_admin`
  - `$_SESSION['discord_id']` et `$_SESSION['discord_username']` sont déjà en session et restent inchangés
- ⚠️ Ne pas recopier le bug `$_SESSION["id"] = $id;` de `register.php` (`$id` n'est pas défini)
- Point d'entrée : un lien « Créer mon compte classique » doit apparaître pour un utilisateur Discord-only
  (là où `$_SESSION['loggedin']` est vrai mais `$_SESSION['username']` vide, ex : `public/src/index/index.php`)

## 6. Sécurité (récapitulatif)
- **State CSRF/OAuth** : généré au rendu du bouton, validé en premier dans le callback,
  consommé une seule fois (`unset`), comparé avec `hash_equals`
  - Sans lui : un attaquant peut lier SON Discord au compte de la victime connectée
  - Généré UNIQUEMENT depuis une route `/discord/*` (voir §3 : le cookie de session
    porte le `SameSite` de la dernière route visitée)
- **SameSite** : `Lax` uniquement sur `/discord/login`, `/discord/callback`, `/discord/register`
  (sinon la session est perdue au retour Discord : state + `loggedin`)
- **Session fixation** : `session_regenerate_id(true)` au moment du login Discord
- **Unicité** : les SELECT puis UPDATE/INSERT ne sont pas atomiques (TOCTOU)
  → catch `PDOException` (23000/1062) au moment de l'ÉCRITURE :
  `discordCreateUser()` et `discordLinkToUser()` (voir §4) et UPDATE `username`
  de `/discord/register` (voir §5)
- **Rate-limit** : `checkRateLimitByIP($pdo, 'discord_callback', 10, 15)`
- **Logs** : `logSecurityEvent` sur `discord_login_success`, `discord_link_success`,
  `discord_link_conflict`, `discord_link_ghost_merged` (avec `ghost_id`),
  `discord_link_error`, `discord_create_race`, `discord_create_error`,
  `discord_state_invalid`, `discord_user_denied`, `discord_token_exchange_failed`
- **Secrets** : uniquement via `.env` (voir `.env.exemple` + README)

## 7. Tests (`tests/`)
- `test_discord_callback_no_state.sh` : `GET /discord/callback?code=x` → doit refuser (state)
- `test_discord_callback_bad_state.sh` : `GET /discord/callback?code=x&state=y` → doit refuser
- Vérifier que la colonne `discord_id` existe et est `UNIQUE` après recréation de la base

## 8. Vérification des 2 parcours

### Parcours A — Discord d'abord, puis compte classique
1. Non connecté → `/discord/login` → « Se connecter avec Discord » → Discord → `/discord/callback`
2. Callback : `discordFindUserByDiscordId` = null → `discordCreateUser` → ligne `username=NULL, password=NULL, discord_id=D`
3. Session : `loggedin=true, id=<ligne>, username="", discord_id=D, discord_username`
4. L'utilisateur voit « Créer mon compte classique » → `/discord/register`
5. `UPDATE users SET username, password WHERE id=<ligne> AND discord_id IS NOT NULL AND username IS NULL` (`rowCount()==1`)
6. Session : `username` = nom classique → l'utilisateur a bien les deux noms
- ✅ Fonctionne (nécessite : gardes + lien de la section 5)

### Parcours B — Compte classique d'abord, puis Discord
1. `/login` classique → `loggedin=true, id, username`
2. `/discord/login` (état connecté) → « Lier mon compte Discord » → Discord → `/discord/callback`
3. Callback : `$alreadyLoggedIn=true` → liaison :
   - propriétaire absent → `discordLinkToUser` (`UPDATE users SET discord_id, discord_linked_at WHERE id=<session>`)
   - propriétaire = ligne fantôme (`username IS NULL`) → suppression puis liaison (fusion)
   - propriétaire = autre compte classique → erreur propre
4. Session : `discord_id` + `discord_username` ajoutés, `username` classique inchangé
- ✅ Fonctionne (nécessite : bouton accessible connecté + fusion du fantôme)

### Points de vigilance restants
- **`is_admin`** : recalculer explicitement comme `login.php` (`strcasecmp($username, ADMIN_USERNAME) === 0`),
  uniquement si `username` non vide ; laisser `false` pour un compte Discord-only
- **Deux onglets / deux `state`** : `$_SESSION['oauth_state']` est unique, un second démarrage de flow
  écrase le premier → le premier callback échouera. Acceptable, mais à savoir
- **`/login` classique ne remplit pas `discord_id`/`discord_username`** : un compte lié qui se reconnecte
  en classique n'aura pas ces infos en session. Optionnel : les charger dans `login.php` après `SELECT`
  pour que les deux noms soient toujours disponibles
- **Mot de passe NULL** : un compte Discord-only sur `/password/update` a `password = NULL`
  → `password_verify($old, null)` renvoie `false` (avertissement PHP 8.1+). À gérer (masquer/renvoyer `/discord/register`)
- **HTTPS en local** : le cookie de session est `Secure` (`index.php`) → en test navigateur sur `http://localhost:8000`
  il faut un tunnel HTTPS, sinon le cookie n'est pas posé (les tests curl passent)

### Décisions actées
- **SameSite** : `Lax` uniquement sur `/discord/login`, `/discord/callback`, `/discord/register` (Strict ailleurs)
- **Callback** : intercepté tôt dans `index.php`, hors template HTML
- **`/discord/register`** : aucun code d'invitation
- **Ancien flow** : `/discord/tools` (implicit grant) conservé en parallèle *(supprimé le 19/10/2026 : page, JS et lien retirés)*

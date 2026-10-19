<?php
// Discord OAuth2 (authorization code, scope identify)
// Partagé par /discord/login (bouton), /discord/callback (handler) et docs
// Secrets uniquement via .env : DISCORD_CLIENT_ID, DISCORD_CLIENT_SECRET, DISCORD_REDIRECT_URI

// Discord est configuré si les 3 variables .env sont non vides
function discordIsConfigured(): bool
{
    $clientId = getenv('DISCORD_CLIENT_ID');
    $clientSecret = getenv('DISCORD_CLIENT_SECRET');
    $redirectUri = getenv('DISCORD_REDIRECT_URI');

    if (empty($clientId) || empty($clientSecret) || empty($redirectUri)) {
        return false;
    }

    return true;
}

// Construit l'URL d'autorisation et stocke le state en session
// ⚠️ À appeler UNIQUEMENT depuis une route /discord/* (le cookie de session
// porte le SameSite de la dernière route visitée)
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

// Échange le code contre un token Discord. Retourne le tableau de réponse ou null
function discordExchangeCode($code)
{
    $postFields = http_build_query([
        'client_id' => getenv('DISCORD_CLIENT_ID'),
        'client_secret' => getenv('DISCORD_CLIENT_SECRET'),
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => getenv('DISCORD_REDIRECT_URI'),
    ]);

    $ch = curl_init("https://discord.com/api/oauth2/token");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        return null;
    }

    $data = json_decode($response, true);
    if (!is_array($data) || empty($data['access_token'])) {
        return null;
    }

    return $data;
}

// Récupère le profil Discord (id, username, avatar, ...). Retourne un tableau ou null
function discordFetchUser($token)
{
    if (!is_array($token) || empty($token['access_token'])) {
        return null;
    }

    $ch = curl_init("https://discord.com/api/users/@me");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Authorization: Bearer ' . $token['access_token'],
        ],
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        return null;
    }

    $user = json_decode($response, true);
    if (!is_array($user) || empty($user['id'])) {
        return null;
    }

    return $user;
}

// Retourne la ligne users pour ce discord_id (id, username, discord_id) ou null
// (sert au login Discord ET à la détection de la ligne "fantôme")
function discordFindUserByDiscordId(PDO $pdo, $discordId)
{
    $stmt = $pdo->prepare("SELECT id, username, discord_id FROM users WHERE discord_id = :discord_id");
    $stmt->bindParam(":discord_id", $discordId, PDO::PARAM_STR);

    if ($stmt->execute()) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        return $row;
    }

    return null;
}

// Crée une ligne Discord-only (username/password NULL). Retourne le nouvel id
function discordCreateUser(PDO $pdo, $discordId): int
{
    $stmt = $pdo->prepare("INSERT INTO users (discord_id, discord_linked_at) VALUES (:discord_id, NOW())");
    $stmt->bindParam(":discord_id", $discordId, PDO::PARAM_STR);
    $stmt->execute();

    return (int)$pdo->lastInsertId();
}

// Lie un discord_id à la ligne users classique existante (UPDATE ... WHERE id = :id)
function discordLinkToUser(PDO $pdo, int $userId, $discordId): void
{
    $stmt = $pdo->prepare("UPDATE users SET discord_id = :discord_id, discord_linked_at = NOW() WHERE id = :id");
    $stmt->bindParam(":discord_id", $discordId, PDO::PARAM_STR);
    $stmt->bindParam(":id", $userId, PDO::PARAM_INT);
    $stmt->execute();
}

// Supprime une ligne users (ligne "fantôme" Discord-only) par id
// ⚠️ FK ON DELETE CASCADE : user_pictures / user_*_views suivent la suppression
function discordDeleteUser(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindParam(":id", $userId, PDO::PARAM_INT);
    $stmt->execute();
}

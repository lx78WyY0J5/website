<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/loadEnv.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/discord_oauth.php';

    echo '<link rel="stylesheet" href="/src/css/discord.css">';

    // Nouveau flow : authorization code, le state est stocké en session au rendu
    // ⚠️ Générés UNIQUEMENT depuis une route /discord/*
    $discordUrl = discordBuildAuthorizeUrl();

    if ($discordUrl !== '') {
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
            echo '<a href="' . $discordUrl . '">Lier mon compte Discord</a>';
        }
        else {
            echo '<a href="' . $discordUrl . '">Se connecter avec Discord</a>';
        }
    }
    else {
        echo '<p>Discord non configuré : renseignez DISCORD_CLIENT_ID, DISCORD_CLIENT_SECRET et DISCORD_REDIRECT_URI dans le .env</p>';
    }
?>

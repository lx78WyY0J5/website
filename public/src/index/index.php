<?php
    // Les deux noms possibles : compte classique et/ou compte Discord
    $classicName = "";
    if (isset($_SESSION['username'])) {
        $classicName = $_SESSION['username'];
    }
    $discordName = "";
    if (isset($_SESSION['discord_username'])) {
        $discordName = $_SESSION['discord_username'];
    }

    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
        require_once $_SERVER['DOCUMENT_ROOT'] . '/src/pages/profile/picture/getPicture.php';

        $userId = intval($_SESSION["id"]);
        $profilePicture = getLatestProfilePictureOfUser($pdo, $userId);
        $discordPicture = getDiscordProfilePictureOfUser($pdo, $userId);

        echo '<link rel="stylesheet" href="/src/index/welcome-user.css">';
        echo '<div id="welcome-user">';
            echo '<div id="welcome-user-classic">';
                if ($classicName !== "") {
                echo '<h2>'. getGreeting() . '</h2>';
                    echo '<h1>' . htmlspecialchars($classicName) .'</h1>';
                }
                if ($profilePicture && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($profilePicture, '/'))) {
                    echo '<img src="/' . ltrim($profilePicture, '/') . '" alt="Photo de profil" class="welcome-user-picture">';
                }
            echo '</div>';
            echo '<div id="welcome-user-discord">';
                if ($discordName !== "") {
                echo '<h2>'. getGreeting() . '</h2>';
                    echo '<h1>' . htmlspecialchars($discordName) .'</h1>';
                }
                if ($discordPicture && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($discordPicture, '/'))) {
                    echo '<img src="/' . ltrim($discordPicture, '/') . '" alt="Photo de profil Discord" class="welcome-user-picture">';
                }
            echo '</div>';
        echo '</div>';
        echo '<div id="user-id">';
        echo '<hr>';
            echo '<h2>Vous avez la session n°' . $_SESSION["id"] . '</h2>';
            if($_SESSION['is_admin'] === true){
                echo '<h2>Compte administrateur</h2>';
            }
        echo '<hr>';
        echo '</div>';
    }

    include $_SERVER['DOCUMENT_ROOT'] . '/src/index/index.html';

    if(!isset($_SESSION['loggedin'])){
        echo '<p><a href="/login">Connectez-vous</a>, ou alors pourquoi ne pas <a href="/register">créer un compte</a> ?</p>';
        echo '<p>Nouveau : <a href="/discord/login">Connectez-vous avec Discord</a> !</p>';
    }
    else {
        if($classicName === "" && isset($_SESSION["discord_id"]) && !empty($_SESSION["discord_id"])){
            echo '<p>Vous êtes connecté via Discord : <a href="/discord/register">créez un compte classique (ou reliez-en un déjà existant)</a><br>Le compte sera lié à votre compte Discord, vous pourrez vous connecter des deux façons</p>';
        }
        else if($classicName !== "" && !isset($_SESSION["discord_id"]) && empty($_SESSION["discord_id"])){
            echo '<p>Nouveau : <a href="/discord/login">Connectez-vous avec Discord</a> !<br>Reliez votre compte à Discord, vous pourrez vous connecter des deux façons</p>';
        }
    }

    $x = rand(0, 10);
    echo '<hr>';
    echo '<br class="padding">';
    if($x == 0){
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/outils/cube/cube.html';
    }
    else if($x == 1){
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/outils/console/console.html';
    }
    else if($x == 2){
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/settings/settings.html';
        echo '<script>loadSettingsSwitch();</script>';
    }
    else if($x == 3){
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/admin/contact/contact.html';
    }
    else if($x == 4){
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/github/statistiques/github-update.html';
    }
    else if($x == 5){
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/github/statistiques/github-commits.html';
    }
    else if($x == 6){
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/github/statistiques/github-events.html';
    }
    else if($x == 7){
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/outils/donut/donut.html';
    }
    else if($x == 8){
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/outils/frames/frames.html';
        echo '<script>setMenuVisible(false); injectAsBackground();</script>';
    }
    else if($x == 9){
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/games/minesweeper/minesweeper.html';
    }
    else if($x == 10){
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/admin/donation/donation.html';
    }
    echo '<br class="padding">';

    if(isset($_SESSION["is_admin"]) && !empty($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true) {
        echo '<hr>';
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/info/info.php';
    }

    function getGreeting(): string
    {
        $greetings = [
            'Bonjour',
            'Bienvenue',
            'Bon retour',
            'Salut',
            'Enchanté',
            // Français
            // Anglais
            'Hello',
            'Hi',
            'Welcome',
            'Hey',
            'Wassup',
            // Autres
            'Hola',         // Espagnol / Catalan
            'Ciao',         // Italien
            'Olá',          // Portugais
            'Oi',           // Portugais (BR)
            'Hallo',        // Allemand / Néerlandais
            'Hoi',          // Néerlandais
            'Hej',          // Suédois / Danois
            'Hei',          // Norvégien / Finnois
            'Halló',        // Islandais
            'Ola',          // Galicien
            'Halò',         // Écossais gaélique

            // Asie
            'Namaste',      // Hindi / Népalais
            'Ni hao',       // Chinois
            'Chào',         // Vietnamien
            'Halo',         // Indonésien
            'Hyālō',        // Bengali

            // Océanie
            'Aloha',        // Havaïen
        ];

        return $greetings[array_rand($greetings)];
    }
?>
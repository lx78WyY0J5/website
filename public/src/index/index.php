<?php
    if(isset($_SESSION["loggedin"]) && isset($_SESSION['username'])){
        require_once $_SERVER['DOCUMENT_ROOT'] . '/src/pages/profile/picture/getPicture.php';

        $userId = intval($_SESSION["id"]);
        $profilePicture = getLatestProfilePictureOfUser($pdo, $userId);

        echo '<link rel="stylesheet" href="/src/index/welcome-user.css">';
        echo '<div id="welcome-user">';
            echo '<h2>Bienvenue</h2>';
            if ($profilePicture && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($profilePicture, '/'))) {
                echo '<img src="/' . ltrim($profilePicture, '/') . '" alt="Photo de profil" class="welcome-user-picture">';
            }
            echo '<h1>' . $_SESSION['username'] .'</h1>';
        echo '</div>';
        echo '<div id="user-id">';
            echo '<h2>Vous avez la session n°' . $_SESSION["id"] . '</h2>';
        echo '</div>';
        echo '<hr>';
    }

    include $_SERVER['DOCUMENT_ROOT'] . '/src/index/index.html';

    if(!isset($_SESSION['loggedin'])){
        echo '<p><a href="/login">Connectez-vous</a>, ou alors pourquoi ne pas <a href="/register">créer un compte</a> ?</p>';
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
?>
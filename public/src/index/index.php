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
        echo '<link rel="stylesheet" href="/src/pages/outils/cube/cube.css">';
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/outils/cube/cube.html';
        echo '<script src="/src/pages/outils/cube/cube.js"></script>';
    }
    else if($x == 1){
        echo '<link rel="stylesheet" href="/src/pages/outils/console/console.css">';
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/outils/console/console.html';
        echo '<script src="/src/pages/outils/console/console.js"></script>';
    }
    else if($x == 2){
        echo '<link rel="stylesheet" href="/src/pages/settings/settings.css">';
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/settings/settings.html';
        echo '<script src="/src/settings.js"></script>';
        echo '<script>loadSettingsSwitch();</script>';
    }
    else if($x == 3){
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/admin/contact/contact.html';
    }
    else if($x == 4){
        echo '<link rel="stylesheet" href="/src/pages/github/statistiques/github-update.css">';
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/github/statistiques/github-update.html';
        echo '<script src="/src/js/gather.js"></script>';
        echo '<script src="/src/pages/github/statistiques/github-update.js"></script>';
    }
    else if($x == 5){
        echo '<link rel="stylesheet" href="/src/pages/github/statistiques/github-commits.css">';
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/github/statistiques/github-commits.html';
        echo '<script src="/src/js/gather.js"></script>';
        echo '<script src="/src/pages/github/statistiques/github-commits.js"></script>';
    }
    else if($x == 6){
        echo '<link rel="stylesheet" href="/src/pages/github/statistiques/github-events.css">';
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/github/statistiques/github-events.html';
        echo '<script src="/src/js/gather.js"></script>';
        echo '<script src="/src/pages/github/statistiques/github-events.js"></script>';
    }
    else if($x == 7){
        echo '<link rel="stylesheet" href="/src/pages/outils/donut/donut.css">';
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/outils/donut/donut.html';
        echo '<script src="/src/pages/outils/donut/donut.js"></script>';
    }
    else if($x == 8){
        echo '<link rel="stylesheet" href="/src/pages/outils/frames/frames.css">';
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/outils/frames/frames.html';
        echo '<script src="/src/pages/outils/frames/frames.js"></script>';
        echo '<script>setMenuVisible(false); injectAsBackground();</script>';
    }
    else if($x == 9){
        echo '<link rel="stylesheet" href="/src/pages/games/minesweeper/minesweeper.css">';
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/games/minesweeper/minesweeper.html';
        echo '<script src="/src/pages/games/minesweeper/minesweeper.js"></script>';
    }
    else if($x == 10){
        echo '<link rel="stylesheet" href="/src/pages/admin/donation/donation.css">';
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/admin/donation/donation.html';
        echo '<script src="/src/pages/admin/donation/donation.js"></script>';
    }
    echo '<br class="padding">';

    if(isset($_SESSION["is_admin"]) && !empty($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true) {
        echo '<hr>';
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/info/info.php';
    }
?>
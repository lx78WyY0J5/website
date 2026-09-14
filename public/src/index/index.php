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
    }

    if(!isset($_SESSION["is_admin"]) || empty($_SESSION["is_admin"]) || $_SESSION["is_admin"] != true) {
        include $_SERVER['DOCUMENT_ROOT'] . '/src/index/index.html';
        if(!isset($_SESSION['loggedin'])){
            echo '<p><a href="/login">Connectez-vous</a>, ou alors pourquoi ne pas <a href="/register">créer un compte</a> ?</p>';
        }
    }
    else{
        echo '<hr>';
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/info/info.php';
    }
?>
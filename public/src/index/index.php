<?php
    if(isset($_SESSION["loggedin"]) && isset($_SESSION['username'])){
        echo '<link rel="stylesheet" href="/src/index/welcome-user.css">';
        echo '<div id="welcome-user">';
        echo '<h2>Bienvenue</h2> <h1>' . $_SESSION['username'] .'</h1>';
        echo '</div>';
    }

    if($_SESSION["is_admin"] !== true){
        include $_SERVER['DOCUMENT_ROOT'] . '/src/index/index.html';
        if(!isset($_SESSION['loggedin'])){
            echo '<p><a href="/login">Connectez-vous</a>, ou alors pourquoi ne pas <a href="/register">créer un compte</a> ?</p>';
        }
    }
    else{
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/info/info.php';
    }
?>
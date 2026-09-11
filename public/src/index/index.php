<?php
    if(isset($_SESSION["loggedin"]) && isset($_SESSION['username'])){
        echo '<h2>Bienvenue ' . $_SESSION['username'] .'</h2>';
    }

    if($_SESSION["is_admin"] !== true){
        include $_SERVER['DOCUMENT_ROOT'] . '/src/index/index.html';
        if(!isset($_SESSION['loggedin'])){
            echo '<p><a href="/login">Connectez-vous</a> ou <a href="/register">créez un compte</a></p>';
        }
    }
    else{
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/info/info.php';
    }
?>
<?php
    include $_SERVER['DOCUMENT_ROOT'] . '/src/php/loadEnv.php';
    include $_SERVER['DOCUMENT_ROOT'] . '/src/php/PDO.php';

    if(isset($_SESSION["loggedin"]) && isset($_SESSION['username'])){
        echo '<h2>Bienvenue ' . $_SESSION['username'] .'</h2>';
    }

    if($_SESSION["is_admin"] !== true){
        include $_SERVER['DOCUMENT_ROOT'] . '/src/index/index.html';
    }
    else{
        include $_SERVER['DOCUMENT_ROOT'] . '/src/php/info.php';
    }
?>
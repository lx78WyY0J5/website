<?php
    if(isset($_SESSION["loggedin"]) && isset($_SESSION['username'])){
        echo '<h2>Bienvenue ' . $_SESSION['username'] .'</h2>';
    }

    include $_SERVER['DOCUMENT_ROOT'] . '/src/index/index.html';
    include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/info/info.php';
?>
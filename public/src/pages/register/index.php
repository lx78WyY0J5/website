<?php
    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    echo "<p>Vous êtes déjà connecté</p>";
    }
    else{
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/register/register.html';
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/register/register.php';
    }
?>
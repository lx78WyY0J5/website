<?php
    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
        
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/password/update/update.html';
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/password/update/update.php';
    }
    else{
        echo "<p>Vous n'êtes pas connecté</p>";
    }
?>
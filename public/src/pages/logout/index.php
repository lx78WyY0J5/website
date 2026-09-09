<?php
    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/logout/logout.html';
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/logout/logout.php';
    }
    else{
        echo "<p>Vous n'êtes pas connecté</p>";
    }
?>
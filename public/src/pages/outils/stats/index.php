<?php
    if(isset($_SESSION["is_admin"]) && !empty($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true) {
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/outils/stats/stats.html';
    }
    else{
        echo '<h1>Fonction administrateur</h1>';
        echo '<p>Merci de vous <a href="/login">connecter</a> en tant qu\'administrateur !</p>';
    }
?>
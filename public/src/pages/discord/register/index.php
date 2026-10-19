<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/discord_oauth.php';

    $discordDisplayName = "";
    if (isset($_SESSION["discord_username"])) {
        $discordDisplayName = $_SESSION["discord_username"];
    }

    // Gardes d'accès :
    // - être connecté (via Discord)
    // - avoir le discord_id en session (vrai uniquement après un flow Discord)
    // - ne PAS avoir déjà de compte classique (username défini)
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || empty($_SESSION["discord_id"]) || !empty($_SESSION["username"])){
        header("location: /");
        echo "<script>window.location.href = '/';</script>";
    }
    else{
        include $_SERVER['DOCUMENT_ROOT'] . '/src/php/csrf.php';
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/discord/register/register.html';
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/discord/register/register.php';
    }
?>

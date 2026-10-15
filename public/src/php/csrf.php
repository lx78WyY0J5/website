<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function generateCsrfToken()
{
    // Regénérer le token s'il n'existe pas déjà
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validateCsrfToken()
{
    $token;

    if(isset($_POST["csrf_token"]) && !empty(trim($_POST["csrf_token"]))){
        $token = $_POST["csrf_token"];
    }

    // Comparer le token avec celui en session
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }

    // Utiliser hash_equals pour éviter les attaques de timing
    return hash_equals($token, $_SESSION['csrf_token']);
}
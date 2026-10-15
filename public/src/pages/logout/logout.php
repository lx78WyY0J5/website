<?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF token
        if(!validateCsrfToken()) {
            echo "<p>Erreur de sécurité : token CSRF invalide</p>";
            exit;
        }

        // Unset all of the session variables
        $_SESSION = array();

        // Destroy the session.
        session_destroy();

        // Redirect to login page
        header("location: /");
        echo "<script>window.location.href = '/';</script>";
        exit;
    }
?>
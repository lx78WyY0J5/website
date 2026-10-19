<?php
//load requiered modules
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/loadEnv.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/PDO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/password_validation.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/logging.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/rate_limiter.php';

// Define variables and initialize with empty values
$username = $password = $confirm_password = "";
$can_register = true;

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Rate limiting (per IP + per user)
    checkRateLimitByIP($pdo, 'discord_register', 5, 15);
    checkRateLimitByUser($pdo, (int)$_SESSION["id"], 'discord_register', 5, 15);

    // Validate CSRF token
    if(!validateCsrfToken()) {
        echo "<p>Erreur de sécurité : token CSRF invalide</p>";
        $can_register = false;
    }

    // Validate username (same rules as register.php)
    if(!isset($_POST["username"]) || !is_string($_POST["username"]) || empty(trim($_POST["username"]))){
        echo "<p>Veuillez saisir un nom d'utilisateur</p>";
        $can_register = false;
    }
    else {
        $username = trim($_POST["username"]);

        if(!preg_match('/^[a-zA-Z0-9_]+$/', $username)){
            echo "<p>Le nom d'utilisateur ne peut contenir que des lettres, chiffres et underscores</p>";
            $can_register = false;
        }

        if(strlen($username) < 6){
            echo "<p>Le nom d'utilisateur doit comporter au moins 6 caractères</p>";
            $can_register = false;
        }

        if(strlen($username) > 24){
            echo "<p>Le nom d'utilisateur doit comporter moins de 24 caractères</p>";
            $can_register = false;
        }

        $adminUsername = getenv("ADMIN_USERNAME");
        if (empty($adminUsername) || !isset($adminUsername)) {
            $adminUsername = "administrator";
        }
        if(strcasecmp($username, $adminUsername) === 0){
            echo "<p>Ce nom d'utilisateur est réservé</p>";
            $can_register = false;
        }
    }

    // Validate password using shared validation
    if (isset($_POST["password"]) && is_string($_POST["password"])) {
        $password = $_POST["password"];
    }
    if (isset($_POST["confirm_password"]) && is_string($_POST["confirm_password"])) {
        $confirm_password = $_POST["confirm_password"];
    }
    if (!checkPassword($password, $confirm_password)) {
        $can_register = false;
    }

    // Check that the username is still available if no error before
    if($can_register === true){
        // Prepare a select statement
        try {
            $sql = "SELECT id FROM users WHERE username = :username";

            if($stmt = $pdo->prepare($sql)){
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);

                // Set parameters
                $param_username = $username;

                // Attempt to execute the prepared statement
                if($stmt->execute()){
                    if($stmt->rowCount() == 1){
                        echo "<p>Ce nom d'utilisateur est déjà pris</p>";
                        $can_register = false;
                    }
                }
                else {
                    echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard</p>";
                    $can_register = false;
                }

                // Close statement
                unset($stmt);
            }
        }
        catch(PDOException $e) {
            echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard</p>";
            $can_register = false;
        }
    }

    // Update the existing Discord-only row (NO new INSERT)
    if($can_register === true){
        $can_register = false;
        try {
            // Prepare an update statement. La condition WHERE garantit que la
            // ligne est bien une ligne Discord-only attendue
            $sql = "UPDATE users SET username = :username, password = :password
                WHERE id = :id AND discord_id IS NOT NULL AND username IS NULL";

            if($stmt = $pdo->prepare($sql)){
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
                $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
                $stmt->bindParam(":id", $param_id, PDO::PARAM_INT);

                // Set parameters
                $param_username = $username;
                $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
                $param_id = (int)$_SESSION["id"];

                // Attempt to execute the prepared statement
                if($stmt->execute()){
                    // Vérifier rowCount() === 1 : la ligne a déjà un username,
                    // ou l'id de session est invalide
                    if($stmt->rowCount() === 1){
                        $can_register = true;
                    }
                    else {
                        echo "<p>Impossible de rattacher le compte : la ligne a déjà un compte classique, ou la session n'est plus valide</p>";
                        logSecurityEvent('discord_register_failed', ['user_id' => $_SESSION['id']]);
                    }
                }
                else {
                    echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard</p>";
                }

                // Close statement
                unset($stmt);
            }
        }
        catch(PDOException $e) {
            if ($e->getCode() === "23000") {
                // TOCTOU sur l'unicité : un autre utilisateur a pris ce username
                // entre le SELECT et l'UPDATE -> même message que register.php
                echo "<p>Ce nom d'utilisateur est déjà pris</p>";
            }
            else {
                echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard</p>";
            }
            logSecurityEvent('discord_register_error', ['user_id' => $_SESSION['id']]);
        }

        // Check input errors before updating the session
        if($can_register === true){
            // Store data in session variables
            $_SESSION["username"] = $username;

            // même logique is_admin que login.php (username non vide ici)
            $adminUsername = getenv("ADMIN_USERNAME");
            if (empty($adminUsername) || !isset($adminUsername)) {
                $adminUsername = "administrator";
            }
            if(strcasecmp($username, $adminUsername) === 0){
                $_SESSION["is_admin"] = true;
            }
            else {
                $_SESSION["is_admin"] = false;
            }

            echo "<script>document.forms[0].style = 'display: none;';</script>";
            logSecurityEvent('discord_register_success', ['user_id' => $_SESSION['id'], 'username' => $username]);
            header("location: /");
            echo "<script>window.location.href = '/';</script>";
        }
    }

    // Close connection
    unset($pdo);
}
?>

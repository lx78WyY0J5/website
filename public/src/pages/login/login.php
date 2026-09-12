<?php
//load requiered modules
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/loadEnv.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/PDO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/logging.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/rate_limiter.php';

// Rate limiting
$clientIp = getClientIdentifier();
if (!checkRateLimit($pdo, $clientIp, 'login', 5, 15)) {
    logSecurityEvent('rate_limit_exceeded', ['endpoint' => 'login', 'ip' => $clientIp]);
    rateLimitExceededResponse('login');
}

// Define variables and initialize with empty values
$username = $password = "";
$can_register = true;

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        echo "<p>Veuillez saisir un nom d'utilisateur</p>";
        $can_register = false;
    } else{
        $username = trim($_POST["username"]);
    }

    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        echo "<p>Veuillez saisir un mot de passe</p>";
        $can_register = false;
    } else{
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if($can_register === true){
        // Prepare a select statement
        $sql = "SELECT id, username, password FROM users WHERE username = :username";

        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);

            // Set parameters
            $param_username = trim($_POST["username"]);

            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Check if username exists, if yes then verify password
                if($stmt->rowCount() == 1){
                    if($row = $stmt->fetch()){
                        $id = $row["id"];
                        $username = $row["username"];
                        $hashed_password = $row["password"];
                        if(password_verify($password, $hashed_password)){
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;

                            if(strcasecmp(trim($_POST["username"]), "administrator") === 0){
                                $_SESSION["is_admin"] = true;
                            } else {
                                $_SESSION["is_admin"] = false;
                            }

                            echo "<script>document.forms[0].style = 'display: none;';</script>";
                            logSecurityEvent('login_success', ['username' => $username]);

                            // Redirect user to welcome page
                            header("location: /");
                            echo "<script>window.location.href = '/';</script>";
                        }
                        else {
                            // Password is not valid, display a generic error message
                            echo "<p>Le nom d'utilisateur ou le mot de passe ne correspond pas</p>";
                            logSecurityEvent('login_failed_password', ['username' => $username]);
                        }
                    }
                } else{
                    // Username doesn't exist, display a generic error message
                    echo "<p>Le nom d'utilisateur ou le mot de passe ne correspond pas</p>";
                    logSecurityEvent('login_failed_username', ['username' => $username]);
                }
            } else{
                echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard</p>";
            }

            // Close statement
            unset($stmt);
        }
    }

    // Close connection
    unset($pdo);
}
?>
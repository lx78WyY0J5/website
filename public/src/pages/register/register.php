<?php
//load requiered modules
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/loadEnv.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/PDO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/password_validation.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/logging.php';

// Define variables and initialize with empty values
$username = $password = $confirm_password = $register_code = "";
$can_register = true;

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate username
    if(!isset($_POST["username"]) || empty(trim($_POST["username"]))){
        echo "<p>Veuillez saisir un nom d'utilisateur</p>";
        $can_register = false;
    }
    else {
        if(!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))){
            echo "<p>Le nom d'utilisateur ne peut contenir que des lettres, chiffres et underscores</p>";
            $can_register = false;
        }

        if(strlen(trim($_POST["username"])) < 6){
            echo "<p>Le nom d'utilisateur doit comporter au moins 6 caractères</p>";
            $can_register = false;
        }

        if(strlen(trim($_POST["username"])) > 24){
            echo "<p>Le nom d'utilisateur doit comporter moins de 24 caractères</p>";
            $can_register = false;
        }

        if(strcasecmp(trim($_POST["username"]), "administrator") === 0){
            echo "<p>Ce nom d'utilisateur est réservé</p>";
            $can_register = false;
        }

        if($can_register === true){
            // Prepare a select statement
            $sql = "SELECT id FROM users WHERE username = :username";

            if($stmt = $pdo->prepare($sql)){
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);

                // Set parameters
                $param_username = trim($_POST["username"]);

                // Attempt to execute the prepared statement
                if($stmt->execute()){
                    if($stmt->rowCount() == 1){
                        echo "<p>Ce nom d'utilisateur est déjà pris</p>";
                        $can_register = false;
                    } else{
                        $username = trim($_POST["username"]);
                    }
                } else{
                    echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard</p>";
                    $can_register = false;
                }

                // Close statement
                unset($stmt);
            }
        }
    }

    //Validate user inviation code
    if(!isset($_POST["code"]) || empty(trim($_POST["code"]))){
        echo "<p>Veuillez saisir le code de registration</p>";
        $can_register = false;
    } else {
        $register_code = trim($_POST["code"]);
        if(trim($_POST["code"]) !== getenv("REGISTER_CODE")){
            echo "<p>Le code de registration est incorrect</p>";
            $can_register = false;
        }
    }

    // Validate password using shared validation
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    if (!checkPassword($password, $confirm_password)) {
        $can_register = false;
    }

    // Check input errors before inserting in database
    if($can_register === true){
        // Prepare an insert statement
        $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";

        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);

            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash

            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Redirect to login page

                // Store data in session variables
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $id;
                $_SESSION["username"] = $username;

                logSecurityEvent('registration', ['username' => $username]);
                header("location: /");
                echo "<script>window.location.href = '/';</script>";
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
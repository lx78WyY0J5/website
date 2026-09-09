<?php
session_start();

// Check if the user is logged in, otherwise redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: /login");
    exit;
}

//load requiered modules
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/loadEnv.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/PDO.php';

// Define variables and initialize with empty values
$password = $confirm_password = "";
$can_update = true;

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate password
    if(empty(trim($_POST["password"]))){
        echo "<p>Veuillez saisir un mot de passe</p>";
        $can_update = false;
    } else {
        if(strlen(trim($_POST["password"])) < 12){
            echo "<p>Le mot de passe doit comporter au moins 12 caractères</p>";
            $can_update = false;
        }

        if(!preg_match('/\d/', trim($_POST["password"]))){
            echo "<p>Le mot de passe doit contenir un chiffre</p>";
            $can_update = false;
        }

        if(!preg_match('/[a-z]/', trim($_POST["password"]))){
            echo "<p>Le mot de passe doit contenir une lettre minuscule</p>";
            $can_update = false;
        }

        if(!preg_match('/[A-Z]/', trim($_POST["password"]))){
            echo "<p>Le mot de passe doit contenir une lettre majuscule</p>";
            $can_update = false;
        }
        
        if(!preg_match('/[^a-zA-Z0-9]/', trim($_POST["password"]))){
            echo "<p>Le mot de passe doit contenir un caractère spécial</p>";
            $can_update = false;
        }
    }

    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        echo "<p>Veuillez confirmer le mot de passe</p>";
        $can_update = false;
    } else{
        $password = trim($_POST["password"]);
        $confirm_password = trim($_POST["confirm_password"]);
        if($password != $confirm_password){
            echo "<p>Les mots de passe ne correspondent pas</p>";
            $can_update = false;
        }
    }

    if(isset($password) && !empty($password)){
        $entropy = calculateEntropy($password);
        echo "<p>L'entropie du mot de passe est de " . round($entropy, 2) . " bits</p>";

        if ($entropy <= 80) {
            echo "<p>L'entropie du mot de passe est trop faible<br>Elle doit être au moins de 80 bits</p>";
        }
    }

    // Check input errors before updating the database
    if($can_update === true){
        // Prepare an update statement
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        
        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
            $stmt->bindParam(":id", $param_id, PDO::PARAM_INT);
            
            // Set parameters
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_id = $_SESSION["id"];
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Password updated successfully. Destroy the session, and redirect to login page
                session_destroy();
                echo "OK mot de passe changé !";
                header("location: /login");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            unset($stmt);
        }
    }
    
    // Close connection
    unset($pdo);
}

function calculateEntropy($password) {
    $L = strlen($password);
    $N = 0;
    
    // Determine pool size based on character types
    if (preg_match('/[a-z]/', $password)) $N += 26;
    if (preg_match('/[A-Z]/', $password)) $N += 26;
    if (preg_match('/[0-9]/', $password)) $N += 10;
    if (preg_match('/[^a-zA-Z0-9]/', $password)) $N += 32; // Symbols

    // Avoid double counting if only one type is present
    if (ctype_alpha($password)) $N = ($password === strtolower($password)) ? 26 : 52;
    if (ctype_digit($password)) $N = 10;

    return $L * log($N, 2);
}
?>
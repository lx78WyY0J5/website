<?php
//load requiered modules
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/loadEnv.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/PDO.php';

// Define variables and initialize with empty values
$username = $password = $confirm_password = "";
$can_register = true;

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate username
    if(empty(trim($_POST["username"]))){
        echo "Veuillez saisir un nom d'utilisateur";
        $can_register = false;
    } else {
        if(!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))){
            echo "Le nom d'utilisateur ne peut contenir que des lettres, chiffres et underscores";
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
                        echo "Ce nom d'utilisateur est déjà pris";
                        $can_register = false;
                    } else{
                        $username = trim($_POST["username"]);
                    }
                } else{
echo "Oups! Une erreur s'est produite. Veuillez réessayer plus tard";
                    $can_register = false;
                }

                // Close statement
                unset($stmt);
            }
        }
    }

    // Validate password
    if(empty(trim($_POST["password"]))){
        echo "Veuillez saisir un mot de passe";
        $can_register = false;
    } else {
        if(strlen(trim($_POST["password"])) < 6){
            echo "Le mot de passe doit comporter au moins 6 caractères";
            $can_register = false;
        } 
        
        if(!preg_match('/\d/', trim($_POST["password"]))){
            echo "Le mot de passe doit contenir un chiffre";
            $can_register = false;
        }
        
        if(!preg_match('/[^a-zA-Z0-9]/', trim($_POST["password"]))){
            echo "Le mot de passe doit contenir un caractère spécial";
            $can_register = false;
        }
    }

    //parse password into var
    if($can_register === true){
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        echo "Veuillez confirmer le mot de passe";
        $can_register = false;
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if($password != $confirm_password){
            echo "Les mots de passe ne correspondent pas";
            $can_register = false;
        }
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
                //header("location: login.php");
                echo "Compte crée !";
            } else{
                echo "Oups! Une erreur s'est produite. Veuillez réessayer plus tard";
            }

            // Close statement
            unset($stmt);
        }
    }

    // Close connection
    unset($pdo);
}
?>
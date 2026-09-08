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
        echo "Please enter a username.";
        $can_register = false;
    } elseif(!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))){
        echo "Username can only contain letters, numbers, and underscores.";
        $can_register = false;
    } else{
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
                    echo "This username is already taken.";
                    $can_register = false;
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
                $can_register = false;
            }

            // Close statement
            unset($stmt);
        }
    }

    // Validate password
    if(empty(trim($_POST["password"]))){
        echo "Please enter a password.";
        $can_register = false;
    } elseif(strlen(trim($_POST["password"])) < 6){
        echo "Password must have atleast 6 characters.";
        $can_register = false;
    } else{
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        echo "Please confirm password.";
        $can_register = false;
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            echo "Password did not match.";
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
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            unset($stmt);
        }
    }

    // Close connection
    unset($pdo);
}
?>
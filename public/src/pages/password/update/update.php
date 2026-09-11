<?php

// Check if the user is logged in, otherwise redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: /login");
    echo "<script>window.location.href = '/login';</script>";
    exit;
}

//load requiered modules
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/loadEnv.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/PDO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/password_validation.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/logging.php';

// Define variables and initialize with empty values
$password = $confirm_password = "";
$can_update = true;

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate password using shared validation
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $old_password = $_POST["old_password"];
    $can_update = checkPassword($password, $confirm_password);

    // Check input errors before updating the database
    if($can_update === true){
    // Verify old password
    $sql = "SELECT password FROM users WHERE id = :id";
    if($stmt = $pdo->prepare($sql)){
        $stmt->bindParam(":id", $param_id, PDO::PARAM_INT);
        $param_id = $_SESSION["id"];

        if($stmt->execute()){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if($row && password_verify($old_password, $row["password"])){
                // Old password is correct — proceed with update
                $sql = "UPDATE users SET password = :password WHERE id = :id";
                if($stmt = $pdo->prepare($sql)){
                    $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
                    $stmt->bindParam(":id", $param_id, PDO::PARAM_INT);

                    $param_password = password_hash($password, PASSWORD_DEFAULT);
                    $param_id = $_SESSION["id"];

                    if($stmt->execute()){
                        logSecurityEvent('password_changed', ['user_id' => $_SESSION['id'], 'username' => $_SESSION['username']]);
                        session_destroy();
                        echo "<p>Mot de passe changé avec succès !</p>";
                        echo "<p>Vous allez être redirigé vers la page de connexion dans 5 secondes</p>";
                        echo "<script>
                            setTimeout(function(){ window.location.href='/login'; }, 5000);
                            document.forms[0].style = 'display: none;';
                        </script>";
                        exit();
                    }
                    else {
                        echo "Oops! Something went wrong. Please try again later.";
                    }
                }
            }
            else {
                echo "<p>L'ancien mot de passe est incorrect</p>";
            }
        }
        else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        unset($stmt);
    }
}

// Close connection
unset($pdo);
}
?>
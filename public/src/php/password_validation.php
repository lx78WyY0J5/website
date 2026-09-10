<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/password_entropy.php';

function checkPassword($password, $password2){
    $password = trim($password);
    $password2 = trim($password2);
    $can_register = true;

    if(empty($password)){
        echo "<p>Veuillez saisir un mot de passe</p>";
        $can_register = false;
    } else {
        if(strlen($password) < 12){
            echo "<p>Le mot de passe doit comporter au moins 12 caractères</p>";
            $can_register = false;
        }

        if(!preg_match('/\d/', $password)){
            echo "<p>Le mot de passe doit contenir un chiffre</p>";
            $can_register = false;
        }

        if(!preg_match('/[a-z]/', $password)){
            echo "<p>Le mot de passe doit contenir une lettre minuscule</p>";
            $can_register = false;
        }

        if(!preg_match('/[A-Z]/', $password)){
            echo "<p>Le mot de passe doit contenir une lettre majuscule</p>";
            $can_register = false;
        }

        if(!preg_match('/[^a-zA-Z0-9]/', $password)){
            echo "<p>Le mot de passe doit contenir un caractère spécial</p>";
            $can_register = false;
        }
    }

    // Validate confirm password
    if(empty($password2)){
        echo "<p>Veuillez confirmer le mot de passe</p>";
        $can_register = false;
    } else{
        if($password != $password2){
            echo "<p>Les mots de passe ne correspondent pas</p>";
            $can_register = false;
        }
    }

    if(isset($password) && !empty($password)){
        $entropy = calculateEntropy($password);

        $entropy_message = getEntropyMessage($entropy);
        if (!isEntropyStrong($entropy)) {
            $can_register = false;
        }
        echo $entropy_message;
    }

    return $can_register;
}

?>
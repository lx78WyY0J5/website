<?php
    if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
        header("location: /");
        echo "<script>window.location.href = '/';</script>";
    }
    else{
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/login/login.html';
        include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/login/login.php';
    }
?>
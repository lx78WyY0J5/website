<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/src/includes/header/header.html';

        if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
            echo "<a href=\"/logout\">Logout</a>";
            echo "<a href=\"/password/update\">Update password</a>";
        }
        else{
            echo "<a href=\"/login\">Login</a>";
            echo "<a href=\"/register\">Register</a>";
        }

?>

</header>
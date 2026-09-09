<!DOCTYPE html>
<html lang="fr">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/login/head.html'; ?>
    <body>
        <?php
            include $_SERVER['DOCUMENT_ROOT'] . '/src/includes/header/header.php';

            session_start();
            if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
               echo "<p>Vous êtes déjà connecté</p>";
            }
            else{
                include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/login/login.html';
                include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/login/login.php';
            }
            include $_SERVER['DOCUMENT_ROOT'] . '/src/includes/footer/footer.html';
        ?>
    </body>
</html>
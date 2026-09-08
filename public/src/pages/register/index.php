<!DOCTYPE html>
<html lang="fr">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/register/head.html'; ?>
    <body>
        <?php
            include $_SERVER['DOCUMENT_ROOT'] . '/src/includes/header/header.html';
            
            session_start();
            if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
               echo "<p>Vous êtes déjà connecté</p>";
            }
            else{
                include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/register/register.html';
                include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/register/register.php';
            }
            include $_SERVER['DOCUMENT_ROOT'] . '/src/includes/footer/footer.html';
        ?>
    </body>
</html>
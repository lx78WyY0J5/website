<!DOCTYPE html>
<html lang="fr">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/logout/head.html'; ?>
    <body>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/src/includes/header/header.php'; ?>
        <article>
            <?php
                if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
                
                    include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/logout/logout.html';
                    include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/logout/logout.php';
                }
                else{
                    echo "<p>Vous n'êtes pas connecté</p>";
                }
            ?>
            </article>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/src/includes/footer/footer.html'; ?>
    </body>
</html>
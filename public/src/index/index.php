<!DOCTYPE html>
<html lang="fr">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/src/index/head.html'; ?>
    <body>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/src/includes/header/header.php'; ?>
        <article>
        <?php
            include $_SERVER['DOCUMENT_ROOT'] . '/src/index/index.html';
            include $_SERVER['DOCUMENT_ROOT'] . '/src/php/loadEnv.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/src/php/PDO.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/src/php/info.php';
        ?>
        </article>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/src/includes/footer/footer.html'; ?>
    </body>
</html>
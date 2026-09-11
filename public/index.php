<?php
    session_start();
    require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/logging.php';

    $uri = trim($_SERVER['REQUEST_URI'], '/');

    if ($uri === '' || $uri === 'accueil') {
        $pageFile = $_SERVER['DOCUMENT_ROOT'] . '/src/index/index.php';
    }
    else {
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/src/pages/' . str_replace('/', DIRECTORY_SEPARATOR, $uri) . '/index.php';
        if (file_exists($filePath)) {
            $pageFile = $filePath;
        }
        else {
            http_response_code(404);
            $pageFile = $_SERVER['DOCUMENT_ROOT'] . '/src/pages/404/404.html';
        }
    }

    if(isset($_SESSION['username'])) {
        logSecurityEvent('visit', ['username' => $_SESSION['username'], 'url' => $uri]);
    }
    else {
        logSecurityEvent('visit', ['url' => $uri]);
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/' . str_replace('/', DIRECTORY_SEPARATOR, $uri) . '/head.html'; ?>
    <link rel="stylesheet" href="/src/css/font.css">
    <link rel="stylesheet" href="/src/css/style.css">
    <link rel="stylesheet" href="/src/css/scrollbar.css">
    <body>
        <link rel="stylesheet" href="/src/css/theme.css">
        <script src="/src/js/theme.js"></script>
        <link rel="stylesheet" href="/src/css/article.css">

        <?php include $_SERVER['DOCUMENT_ROOT'] . '/src/includes/header/header.php'; ?>


        <div id="content">
            <div id="content-left" style="display: none;">
            </div>

            <article id="contentArticle">
                <?php include $pageFile; ?>
            </article>

            <div id="content-right">
                <?php include $_SERVER['DOCUMENT_ROOT'] . '/src/includes/anchor.php'; ?>
            </div>
        </div>

        <?php include $_SERVER['DOCUMENT_ROOT'] . '/src/includes/footer/footer.php'; ?>
    </body>
</html>
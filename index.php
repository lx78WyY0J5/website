<?php
$uri = trim($_SERVER['REQUEST_URI'], '/');

if ($uri === '' || $uri === 'accueil') {
    $pageFile = __DIR__ . '/src/pages/accueil.php';
}
else {
    $filePath = __DIR__ . '/src/pages/' . str_replace('/', DIRECTORY_SEPARATOR, $uri) . '.php';
    if (file_exists($filePath)) {
        $pageFile = $filePath;
    }
    else {
        http_response_code(404);
        echo '404 Not Found';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Site Web</title>
        <link rel="stylesheet" href="src/index/index.css">
        <script src="src/index/index.js"></script>
    </head>
    <body>
        <?php
            include __DIR__ . '/src/includes/header/header.html';
            include $pageFile;
            include __DIR__ . '/src/php/info.php';
            include __DIR__ . '/src/includes/footer/footer.html';
        ?>
    </body>
</html>
<?php
    $uri = trim($_SERVER['REQUEST_URI'], '/');

    if ($uri === '' || $uri === 'accueil') {
        $pageFile = $_SERVER['DOCUMENT_ROOT'] . '/src/index/index.php';
    }
    else {
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/src/pages/' . str_replace('/', DIRECTORY_SEPARATOR, $uri) . '.php';
        if (file_exists($filePath)) {
            $pageFile = $filePath;
        }
        else {
            http_response_code(404);
            echo '404 Not Found';
        }
    }

    include $pageFile;
?>
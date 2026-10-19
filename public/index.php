<?php
    $renderStartTime = microtime(true);
    ob_start();
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($path !== '/' && file_exists(__DIR__ . $path)) {
        return false;
    }

    $sessionPath = "";
    if (is_string($path)) {
        $sessionPath = rtrim($path, '/');
    }

    // SameSite Lax UNIQUEMENT sur les routes du flow Discord : un cookie Strict
    // n'est pas envoyé au retour cross-site depuis Discord (top-level GET),
    // ce qui perdrait la session (state OAuth + loggedin). Strict partout ailleurs.
    $sessionSameSite = 'Strict';
    if ($sessionPath === '/discord/login' || $sessionPath === '/discord/callback' || $sessionPath === '/discord/register') {
        $sessionSameSite = 'Lax';
    }

    session_set_cookie_params([
        'httponly' => true,
        'secure'   => true,
        'samesite' => $sessionSameSite,
    ]);

    session_start();
    require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/logging.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/loadEnv.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/PDO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/rate_limiter.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/view_counter.php';

    // Global rate limit per IP (DDoS protection)
    $clientIp = getClientIdentifier();
    $maxAttempts = 20;
    if(!empty($_SESSION['username']) || !empty($_SESSION['discord_id'])){ // Determine max attempts based on user authentication (classic or Discord)
        $maxAttempts = 50;
    };
    checkRateLimitByIP($pdo, 'global', $maxAttempts, 1);

    $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

    // Route API (case-insensitive, file-based routing)
    $lowerUri = strtolower($uri);
    if (str_starts_with($lowerUri, 'api')) {
        logSecurityEvent('api_visit', ['url' => $uri]);
        $userId = null;
        if(isset($_SESSION['id'])){
            $userId = (int)$_SESSION['id'];
        }
        trackViewAPI($pdo, $uri, $userId); //Increment SQL view counter

        $route = ltrim(str_replace('api', '', $lowerUri), '/');
        if ($route === '') {
            $route = 'index';
        }

        // Try to load the corresponding API file
        $apiFile = $_SERVER['DOCUMENT_ROOT'] . '/src/php/api/' . $route . '.php';
        if (file_exists($apiFile)) {
            require $apiFile;

            exit;
        }

        // Fichier introuvable : 404
        http_response_code(404);
        echo 'Route API inconnue';
        exit;
    }

    if($uri === 'health'){
        logSecurityEvent('health-scan', ['url' => $uri]);
        $now = new DateTimeImmutable('now', new DateTimeZone('Europe/Paris'));
        echo $now->format('Y-m-d H:i:s');
        exit;
    }
    else if($uri === 'v1/models'){
        logSecurityEvent('model-scan', ['url' => $uri]);
        exit;
    }
    else if($uri === 'discord/callback'){
        // Callback OAuth Discord : intercepté tôt (hors template HTML, pas de tracking)
        require $_SERVER['DOCUMENT_ROOT'] . '/src/pages/discord/callback/index.php';
        exit;
    }

    if ($uri === '' || $uri === 'accueil') {
        $pageFile = $_SERVER['DOCUMENT_ROOT'] . '/src/index/index.php';
        include $_SERVER['DOCUMENT_ROOT'] . '/src/index/head.php';
    }
    else {
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/src/pages/' . str_replace('/', DIRECTORY_SEPARATOR, $uri) . '/index.php';
        if (file_exists($filePath)) {
            $pageFile = $filePath;
            include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/' . str_replace('/', DIRECTORY_SEPARATOR, $uri) . '/head.php';
        }
        else {
            http_response_code(404);
            $pageFile = $_SERVER['DOCUMENT_ROOT'] . '/src/pages/404/404.html';
        }
    }

    $userId = null;
        if(isset($_SESSION['id'])){
            $userId = (int)$_SESSION['id'];
        }
    trackView($pdo, $uri, $userId); //Increment SQL view counter

    $viewStats = getViewStats($pdo, $uri, $userId); //used later in the footer to display stats

    $visitContext = ['url' => $uri];
    if(!empty($_SESSION['username'])) {
        $visitContext['username'] = $_SESSION['username'];
    }
    if(!empty($_SESSION['discord_username'])) {
        $visitContext['discord_username'] = $_SESSION['discord_username'];
    }
    logSecurityEvent('visit', $visitContext);
?>

<!DOCTYPE html>
<html lang="fr">
    <link rel="stylesheet" href="/src/css/font.css">
    <link rel="stylesheet" href="/src/css/style.css">
    <link rel="stylesheet" href="/src/css/scrollbar.css">
    <link rel="stylesheet" href="/src/css/theme.css">
    <link rel="stylesheet" href="/src/css/article.css">
    <body>
        <script src="/src/js/theme.js"></script>
        <script src="/src/js/visitor.js"></script>
        <script src="/src/js/visitor-function.js"></script>
        <script src="/src/js/visitor-event.js"></script>

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
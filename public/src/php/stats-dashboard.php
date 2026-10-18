<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/loadEnv.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/PDO.php';
    session_start();
    if(isset($_SESSION["is_admin"]) && !empty($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true) {

    $servername = getenv('DB_IP');
    $username = getenv('DB_USER');
    $password = getenv('DB_PASS');
    $dbname = getenv('DB_NAME');

    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

    // Récupérer les visites avec IP et score de détection
    $stmt = $pdo->query("
        SELECT ip, date_visite, source, headless_score, user_agent, fingerprint, oscpu, platform, path, languages, gpu, plugins, time_spent
        FROM visites 
        ORDER BY date_visite DESC 
        LIMIT 100
    ");
    $visites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les utilisateurs avec date création
    $stmt = $pdo->query("
        SELECT id, username, created_at, 
            (SELECT total_views FROM user_total_views WHERE user_id = users.id) as total_views,
            (SELECT total_views FROM user_api_total_views WHERE user_id = users.id) as total_api_views
        FROM users 
        ORDER BY created_at DESC 
        LIMIT 50
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les statistiques générales
    $stmt = $pdo->query("SELECT total_views FROM site_views WHERE id = 1");
    $siteViews = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalVisits = $siteViews ? (int)$siteViews['total_views'] : 0;

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalUsers = $userCount ? (int)$userCount['count'] : 0;

    $stmt = $pdo->query("SELECT view_count,url FROM page_views WHERE url <> '' ORDER BY view_count DESC LIMIT 1");
    $topPage = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalPageViews = $topPage ? (int)$topPage['view_count'] : 0;
    $totalPageViewsUrl = $topPage ? (String)$topPage['url'] : "0";

    echo json_encode([
        'success' => true,
        'visites' => $visites,
        'users' => $users,
        'total_visits' => $totalVisits,
        'total_users' => $totalUsers,
        'total_page_views' => $totalPageViews,
        'total_page_views_url' => $totalPageViewsUrl
    ]);
    }
    else {
        echo 'fuck you hacker';
    }
?>
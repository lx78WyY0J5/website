<?php
// API endpoint : /api/data
// Reçoit les données de visite collectées en JavaScript et les stocke en base de données.
// Ces données servent au fingerprinting et à la détection de bots dans le trafic.

require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/loadEnv.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/PDO.php';

header('Content-Type: application/json; charset=utf-8');

// Seulement les requêtes POST sont acceptées
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Methode non autorisee. Utilisez POST.'
    ]);
    exit;
}

// Lire le corps de la requete en JSON
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!is_array($data)) {
    $data = [];
}

// Recuperer l'IP reelle du visiteur (cote serveur, fiable)
if (isset($_SERVER['REMOTE_ADDR'])) {
    $visitorIp = $_SERVER['REMOTE_ADDR'];
} else {
    $visitorIp = 'unknown';
}

// Date de la visite (heure du serveur pour garantir l'unicite de la cle primaire)
$visitDate = date('Y-m-d H:i:s.u');

// Champs envoyes par le JavaScript
if (isset($data['user_agent'])) {
    $userAgent = (string)$data['user_agent'];
} else {
    $userAgent = '';
}

if (isset($data['time_zone'])) {
    $timeZone = (string)$data['time_zone'];
} else {
    $timeZone = '';
}

if (isset($data['language'])) {
    $language = (string)$data['language'];
} else {
    $language = '';
}

if (isset($data['platform'])) {
    $platform = (string)$data['platform'];
} else {
    $platform = '';
}

if (isset($data['screen_resolution'])) {
    $screenResolution = (string)$data['screen_resolution'];
} else {
    $screenResolution = '';
}

if (isset($data['color_depth'])) {
    $colorDepth = (int)$data['color_depth'];
} else {
    $colorDepth = 0;
}

if (isset($data['gpu'])) {
    $gpu = (string)$data['gpu'];
} else {
    $gpu = '';
}

if (isset($data['fingerprint'])) {
    $fingerprint = (string)$data['fingerprint'];
} else {
    $fingerprint = '';
}

if (isset($data['referrer'])) {
    $referrer = (string)$data['referrer'];
} else {
    $referrer = '';
}

if (isset($data['path'])) {
    $path = (string)$data['path'];
} else {
    $path = '';
}

if (isset($data['cookies_enabled'])) {
    $cookiesEnabled = (int)$data['cookies_enabled'];
} else {
    $cookiesEnabled = 0;
}

if (isset($data['do_not_track'])) {
    $doNotTrack = (int)$data['do_not_track'];
} else {
    $doNotTrack = 0;
}

// Copie brute des donnees reçues (utile pour le debug et l'evolution)
$rawData = json_encode($data, JSON_UNESCAPED_UNICODE);

try {
    $sql = "
        INSERT INTO `visites` (
            `ip`, `date_visite`, `user_agent`, `time_zone`, `language`,
            `platform`, `screen_resolution`, `color_depth`, `gpu`,
            `fingerprint`, `referrer`, `path`, `cookies_enabled`, `do_not_track`, `raw_data`
        ) VALUES (
            :ip, :date_visite, :user_agent, :time_zone, :language,
            :platform, :screen_resolution, :color_depth, :gpu,
            :fingerprint, :referrer, :path, :cookies_enabled, :do_not_track, :raw_data
        )
        ON DUPLICATE KEY UPDATE
            `user_agent` = :user_agent,
            `time_zone` = :time_zone,
            `language` = :language,
            `platform` = :platform,
            `screen_resolution` = :screen_resolution,
            `color_depth` = :color_depth,
            `gpu` = :gpu,
            `fingerprint` = :fingerprint,
            `referrer` = :referrer,
            `path` = :path,
            `cookies_enabled` = :cookies_enabled,
            `do_not_track` = :do_not_track,
            `raw_data` = :raw_data
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':ip', $visitorIp, PDO::PARAM_STR);
    $stmt->bindParam(':date_visite', $visitDate, PDO::PARAM_STR);
    $stmt->bindParam(':user_agent', $userAgent, PDO::PARAM_STR);
    $stmt->bindParam(':time_zone', $timeZone, PDO::PARAM_STR);
    $stmt->bindParam(':language', $language, PDO::PARAM_STR);
    $stmt->bindParam(':platform', $platform, PDO::PARAM_STR);
    $stmt->bindParam(':screen_resolution', $screenResolution, PDO::PARAM_STR);
    $stmt->bindParam(':color_depth', $colorDepth, PDO::PARAM_INT);
    $stmt->bindParam(':gpu', $gpu, PDO::PARAM_STR);
    $stmt->bindParam(':fingerprint', $fingerprint, PDO::PARAM_STR);
    $stmt->bindParam(':referrer', $referrer, PDO::PARAM_STR);
    $stmt->bindParam(':path', $path, PDO::PARAM_STR);
    $stmt->bindParam(':cookies_enabled', $cookiesEnabled, PDO::PARAM_INT);
    $stmt->bindParam(':do_not_track', $doNotTrack, PDO::PARAM_INT);
    $stmt->bindParam(':raw_data', $rawData, PDO::PARAM_STR);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Visite enregistree',
        'ip' => $visitorIp,
        'date_visite' => $visitDate
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur base de donnees : ' . $e->getMessage()
    ]);
}

$pdo = null;

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

if (isset($data['webdriver'])) {
    $webdriver = (int)$data['webdriver'];
} else {
    $webdriver = 0;
}

if (isset($data['app_version'])) {
    $appVersion = (string)$data['app_version'];
} else {
    $appVersion = '';
}

if (isset($data['timezone_offset'])) {
    $timezoneOffset = (int)$data['timezone_offset'];
} else {
    $timezoneOffset = 0;
}

if (isset($data['battery_level'])) {
    $batteryLevel = (string)$data['battery_level'];
} else {
    $batteryLevel = '0.00';
}

if (isset($data['battery_charging'])) {
    $batteryCharging = (int)$data['battery_charging'];
} else {
    $batteryCharging = 0;
}

if (isset($data['is_mobile'])) {
    $isMobile = (int)$data['is_mobile'];
} else {
    $isMobile = 0;
}

if (isset($data['languages'])) {
    $languages = (string)$data['languages'];
} else {
    $languages = '';
}

if (isset($data['vendor'])) {
    $vendor = (string)$data['vendor'];
} else {
    $vendor = '';
}

if (isset($data['user_agent_brands'])) {
    $userAgentBrands = (string)$data['user_agent_brands'];
} else {
    $userAgentBrands = '';
}

if (isset($data['plugins'])) {
    $plugins = (string)$data['plugins'];
} else {
    $plugins = '';
}

if (isset($data['app_name'])) {
    $appName = (string)$data['app_name'];
} else {
    $appName = '';
}

if (isset($data['app_code_name'])) {
    $appCodeName = (string)$data['app_code_name'];
} else {
    $appCodeName = '';
}

if (isset($data['product_sub'])) {
    $productSub = (string)$data['product_sub'];
} else {
    $productSub = '';
}

if (isset($data['product'])) {
    $product = (string)$data['product'];
} else {
    $product = '';
}

if (isset($data['oscpu'])) {
    $oscpu = (string)$data['oscpu'];
} else {
    $oscpu = '';
}

if (isset($data['headless_score'])) {
    $headlessScore = (int)$data['headless_score'];
} else {
    $headlessScore = 0;
}

if (isset($data['device_memory'])) {
    $deviceMemory = (string)$data['device_memory'];
} else {
    $deviceMemory = '0.00';
}

if (isset($data['hardware_concurrency'])) {
    $hardwareConcurrency = (int)$data['hardware_concurrency'];
} else {
    $hardwareConcurrency = 0;
}

if (isset($data['connection_effective_type'])) {
    $connectionEffectiveType = (string)$data['connection_effective_type'];
} else {
    $connectionEffectiveType = '';
}

if (isset($data['connection_downlink'])) {
    $connectionDownlink = (string)$data['connection_downlink'];
} else {
    $connectionDownlink = '0.00';
}

if (isset($data['connection_save_data'])) {
    $connectionSaveData = (int)$data['connection_save_data'];
} else {
    $connectionSaveData = 0;
}

if (isset($data['on_line'])) {
    $onLine = (int)$data['on_line'];
} else {
    $onLine = 0;
}

if (isset($data['java_enabled'])) {
    $javaEnabled = (int)$data['java_enabled'];
} else {
    $javaEnabled = 0;
}

if (isset($data['gpu_adapter'])) {
    $gpuAdapter = (string)$data['gpu_adapter'];
} else {
    $gpuAdapter = '';
}

if (isset($data['pixel_depth'])) {
    $pixelDepth = (int)$data['pixel_depth'];
} else {
    $pixelDepth = 0;
}

if (isset($data['local_ip'])) {
    $localIp = (string)$data['local_ip'];
} else {
    $localIp = '';
}

// Copie brute des donnees reçues (utile pour le debug et l'evolution)
$rawData = json_encode($data, JSON_UNESCAPED_UNICODE);

try {
    $sql = "
        INSERT INTO `visites` (
            `ip`, `date_visite`,
            `user_agent`, `app_version`, `app_name`, `app_code_name`,
            `product`, `product_sub`, `vendor`, `oscpu`, `platform`,
            `language`, `languages`,
            `webdriver`, `java_enabled`, `is_mobile`, `user_agent_brands`,
            `plugins`,
            `headless_score`,
            `device_memory`, `hardware_concurrency`,
            `time_zone`, `timezone_offset`, `on_line`,
            `connection_effective_type`, `connection_downlink`, `connection_save_data`, `local_ip`,
            `screen_resolution`, `color_depth`, `pixel_depth`,
            `gpu`, `gpu_adapter`,
            `battery_level`, `battery_charging`,
            `cookies_enabled`, `do_not_track`, `referrer`, `path`,
            `fingerprint`, `raw_data`
        ) VALUES (
            :ip, :date_visite,
            :user_agent, :app_version, :app_name, :app_code_name,
            :product, :product_sub, :vendor, :oscpu, :platform,
            :language, :languages,
            :webdriver, :java_enabled, :is_mobile, :user_agent_brands,
            :plugins,
            :headless_score,
            :device_memory, :hardware_concurrency,
            :time_zone, :timezone_offset, :on_line,
            :connection_effective_type, :connection_downlink, :connection_save_data, :local_ip,
            :screen_resolution, :color_depth, :pixel_depth,
            :gpu, :gpu_adapter,
            :battery_level, :battery_charging,
            :cookies_enabled, :do_not_track, :referrer, :path,
            :fingerprint, :raw_data
        )
        ON DUPLICATE KEY UPDATE
            `user_agent` = :user_agent,
            `app_version` = :app_version,
            `app_name` = :app_name,
            `app_code_name` = :app_code_name,
            `product` = :product,
            `product_sub` = :product_sub,
            `vendor` = :vendor,
            `oscpu` = :oscpu,
            `platform` = :platform,
            `language` = :language,
            `languages` = :languages,
            `webdriver` = :webdriver,
            `java_enabled` = :java_enabled,
            `is_mobile` = :is_mobile,
            `user_agent_brands` = :user_agent_brands,
            `plugins` = :plugins,
            `headless_score` = :headless_score,
            `device_memory` = :device_memory,
            `hardware_concurrency` = :hardware_concurrency,
            `time_zone` = :time_zone,
            `timezone_offset` = :timezone_offset,
            `on_line` = :on_line,
            `connection_effective_type` = :connection_effective_type,
            `connection_downlink` = :connection_downlink,
            `connection_save_data` = :connection_save_data,
            `local_ip` = :local_ip,
            `screen_resolution` = :screen_resolution,
            `color_depth` = :color_depth,
            `pixel_depth` = :pixel_depth,
            `gpu` = :gpu,
            `gpu_adapter` = :gpu_adapter,
            `battery_level` = :battery_level,
            `battery_charging` = :battery_charging,
            `cookies_enabled` = :cookies_enabled,
            `do_not_track` = :do_not_track,
            `referrer` = :referrer,
            `path` = :path,
            `fingerprint` = :fingerprint,
            `raw_data` = :raw_data
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':ip', $visitorIp, PDO::PARAM_STR);
    $stmt->bindParam(':date_visite', $visitDate, PDO::PARAM_STR);
    $stmt->bindParam(':user_agent', $userAgent, PDO::PARAM_STR);
    $stmt->bindParam(':app_version', $appVersion, PDO::PARAM_STR);
    $stmt->bindParam(':app_name', $appName, PDO::PARAM_STR);
    $stmt->bindParam(':app_code_name', $appCodeName, PDO::PARAM_STR);
    $stmt->bindParam(':product', $product, PDO::PARAM_STR);
    $stmt->bindParam(':product_sub', $productSub, PDO::PARAM_STR);
    $stmt->bindParam(':vendor', $vendor, PDO::PARAM_STR);
    $stmt->bindParam(':oscpu', $oscpu, PDO::PARAM_STR);
    $stmt->bindParam(':platform', $platform, PDO::PARAM_STR);
    $stmt->bindParam(':language', $language, PDO::PARAM_STR);
    $stmt->bindParam(':languages', $languages, PDO::PARAM_STR);
    $stmt->bindParam(':webdriver', $webdriver, PDO::PARAM_INT);
    $stmt->bindParam(':java_enabled', $javaEnabled, PDO::PARAM_INT);
    $stmt->bindParam(':is_mobile', $isMobile, PDO::PARAM_INT);
    $stmt->bindParam(':user_agent_brands', $userAgentBrands, PDO::PARAM_STR);
    $stmt->bindParam(':plugins', $plugins, PDO::PARAM_STR);
    $stmt->bindParam(':headless_score', $headlessScore, PDO::PARAM_INT);
    $stmt->bindParam(':device_memory', $deviceMemory, PDO::PARAM_STR);
    $stmt->bindParam(':hardware_concurrency', $hardwareConcurrency, PDO::PARAM_INT);
    $stmt->bindParam(':time_zone', $timeZone, PDO::PARAM_STR);
    $stmt->bindParam(':timezone_offset', $timezoneOffset, PDO::PARAM_INT);
    $stmt->bindParam(':on_line', $onLine, PDO::PARAM_INT);
    $stmt->bindParam(':connection_effective_type', $connectionEffectiveType, PDO::PARAM_STR);
    $stmt->bindParam(':connection_downlink', $connectionDownlink, PDO::PARAM_STR);
    $stmt->bindParam(':connection_save_data', $connectionSaveData, PDO::PARAM_INT);
    $stmt->bindParam(':local_ip', $localIp, PDO::PARAM_STR);
    $stmt->bindParam(':screen_resolution', $screenResolution, PDO::PARAM_STR);
    $stmt->bindParam(':color_depth', $colorDepth, PDO::PARAM_INT);
    $stmt->bindParam(':pixel_depth', $pixelDepth, PDO::PARAM_INT);
    $stmt->bindParam(':gpu', $gpu, PDO::PARAM_STR);
    $stmt->bindParam(':gpu_adapter', $gpuAdapter, PDO::PARAM_STR);
    $stmt->bindParam(':battery_level', $batteryLevel, PDO::PARAM_STR);
    $stmt->bindParam(':battery_charging', $batteryCharging, PDO::PARAM_INT);
    $stmt->bindParam(':cookies_enabled', $cookiesEnabled, PDO::PARAM_INT);
    $stmt->bindParam(':do_not_track', $doNotTrack, PDO::PARAM_INT);
    $stmt->bindParam(':referrer', $referrer, PDO::PARAM_STR);
    $stmt->bindParam(':path', $path, PDO::PARAM_STR);
    $stmt->bindParam(':fingerprint', $fingerprint, PDO::PARAM_STR);
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

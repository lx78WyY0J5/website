
<?php
    if(isset($_SESSION["is_admin"]) && !empty($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true) {
        echo '<div id="info">';
            include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/info/info.html';

            echo '<pre>' . shell_exec("echo '{\"modules\":[]}' | fastfetch --logo logosource --config -") . '</pre>';
            echo '<p>PHP v°' . phpversion() . '</p>';
            echo '<p>OS : ' . PHP_OS . "   " . trim(shell_exec("whoami")) . '@' . shell_exec("hostnamectl hostname") . '</p>';
            echo '<p>' . shell_exec('uname -r') . '</p>';

            echo '<p>Document root : ' . $_SERVER['DOCUMENT_ROOT'] . '</p>';

try {
    // --- CPU ---
    if (function_exists('sys_getloadavg')) {
        $cpuLoad = sys_getloadavg();
        if ($cpuLoad !== false) {
            echo '<p>CPU (1 min) : ' . $cpuLoad[0] . '</p>';
        } else {
            echo '<p>CPU : indisponible</p>';
        }
    } else {
        // Fallback : lire /proc/loadavg
        $loadavg = shell_exec("fastfetch -s loadavg --logo none");
        if ($loadavg) {
            echo '<p>CPU : ' . $loadavg . '</p>';
        } else {
            echo '<p>CPU : indisponible</p>';
        }
    }

    // --- RAM ---
    // /proc/meminfo est toujours disponible sur Android/Termux
    $meminfo = @file_get_contents('/proc/meminfo');
    if ($meminfo) {
        preg_match('/^MemTotal:\s+(\d+)\s+kB/m', $meminfo, $mt);
        preg_match('/^MemAvailable:\s+(\d+)\s+kB/m', $meminfo, $ma);
        if ($mt && $ma) {
            $memTotal = (int)$mt[1];           // en KB
            $memAvail = (int)$ma[1];           // en KB
            $memUsed  = $memTotal - $memAvail; // en KB
            $percent  = round(($memUsed / $memTotal) * 100, 2);
            $usedGB   = round($memUsed / 1024 / 1024, 2);
            $totalGB  = round($memTotal / 1024 / 1024, 2);
            echo '<p>RAM : ' . $usedGB . ' Go / ' . $totalGB . ' Go (' . $percent . '%)</p>';
        }
    } else {
        echo '<p>RAM : indisponible</p>';
    }

} catch (\Throwable $e) {
    echo '<p>Erreur : ' . htmlspecialchars($e->getMessage()) . '</p>';
}
        echo '</div>';
        echo '<div id="db-create">';
            require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/create-db.php';
        echo '</div>';
        /*echo '<div id="php-info">';
            phpinfo(); //avoid PHP info as it override with some CSS
        echo '</div>';*/
    }else{
        header("location: /");
        echo "<script>window.location.href = '/';</script>";
    }
?>
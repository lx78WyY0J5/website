
<?php
    if($_SESSION["is_admin"] === true){
        echo '<div id="info">';
            include $_SERVER['DOCUMENT_ROOT'] . '/src/pages/info/info.html';

            echo '<pre>' . shell_exec("echo '{\"modules\":[]}' | fastfetch --logo arch --config -") . '</pre>';
            echo '<p>PHP v°' . phpversion() . '</p>';
            echo '<p>OS : ' . PHP_OS . "   " . trim(shell_exec("whoami")) . '@' . shell_exec("hostnamectl hostname") . '</p>';
            echo '<p>' . shell_exec('uname -r') . '</p>';

            echo '<p>Document root : ' . $_SERVER['DOCUMENT_ROOT'] . '</p>';

            $cpuLoad = sys_getloadavg();
            $cpuUsage = $cpuLoad[0]; // 1-minute load average
            echo '<p>CPU Average (1 min) : ' . $cpuUsage . '</p>';

            // RAM Usage
            $free = shell_exec('free');
            $free = (string)trim($free);
            $free_arr = explode("\n", $free);
            $mem = explode(" ", $free_arr[1]);
            $mem = array_filter($mem);
            $mem = array_merge($mem);

            $memTotal = $mem[1];
            $memUsed  = $mem[2];
            $memUsagePercent = round(($memUsed / $memTotal) * 100, 2);
            echo '<p>RAM : ' . round(($memUsed/1024/1024), 1) . 'Gb /' . round(($memTotal/1024/1024), 1) . 'Gb (' . $memUsagePercent . '%)</p>';
        echo '</div>';
        }
?>
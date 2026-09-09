
<?php

echo '<p>Hello World from PHP!</p>';

echo '<p>PHP version : ' . phpversion() . '</p>';

echo '<pre>' . shell_exec("echo '{\"modules\":[]}' | fastfetch --logo arch --config -") . '</pre>';
echo '<p>OS : ' . PHP_OS . "   " . trim(shell_exec("whoami")) . '@' . shell_exec("hostnamectl hostname") . '</p>';
echo '<p>' . shell_exec('uname -a') . '</p>';

echo '<p>Document root : ' . $_SERVER['DOCUMENT_ROOT'] . '</p>';

?>
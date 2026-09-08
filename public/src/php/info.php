
<?php

echo '<p>Hello World from PHP!</p>';

echo '<hr>';

echo '<p>PHP version : ' . phpversion() . '</p>';

echo '<hr>';

echo '<p>OS : ' . PHP_OS . '</p>';
echo '<p>' . trim(shell_exec("whoami")) . '@' . shell_exec("hostnamectl hostname") . ' : ' . shell_exec("date") . '</p>';
echo '<p>Uname -a : ' . shell_exec('uname -a') . '</p>';

echo '<hr>';

echo '<p>Document root : ' . $_SERVER['DOCUMENT_ROOT'] . '</p>';

?>
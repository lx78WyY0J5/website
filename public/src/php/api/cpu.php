<?php
// API endpoint : /api/uptime
// Retourne le temps d'exécution du serveur en secondes

header('Content-Type: text/plain');
$cmd = 'top -bn1 | grep "Cpu(s)" | awk "{print 100-\$8\"%\"}"';
exec($cmd, $output, $return_var);
echo $output[0] . "\n";
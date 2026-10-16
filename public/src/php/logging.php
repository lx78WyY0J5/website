<?php
function logSecurityEvent(string $event, array $context = []): void {
    $logDir = __DIR__ . '/../../../logs';
    if (!is_dir($logDir)) mkdir($logDir, 0750, true);

    $entry = [
        'time' => date('c'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'none',
        'event' => $event,
        'context' => $context,
    ];

    file_put_contents(
        $logDir . '/security.log',
        json_encode($entry) . "\n",
        FILE_APPEND | LOCK_EX
    );
}
?>
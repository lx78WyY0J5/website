<?php

function checkRateLimit(PDO $pdo, string $identifier, string $endpoint, int $maxAttempts = 5, int $windowMinutes = 15): bool {
    $windowStart = date('Y-m-d H:i:s', strtotime("-{$windowMinutes} minutes"));

    $sql = "SELECT attempts, window_start FROM rate_limits 
            WHERE identifier = :identifier AND endpoint = :endpoint 
            AND window_start >= :window_start";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
    $stmt->bindParam(':endpoint', $endpoint, PDO::PARAM_STR);
    $stmt->bindParam(':window_start', $windowStart, PDO::PARAM_STR);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        if ($row['attempts'] >= $maxAttempts) {
            return false;
        }
        $newAttempts = $row['attempts'] + 1;
        $sql = "UPDATE rate_limits SET attempts = :attempts WHERE identifier = :identifier AND endpoint = :endpoint";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':attempts', $newAttempts, PDO::PARAM_INT);
        $stmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
        $stmt->bindParam(':endpoint', $endpoint, PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $sql = "INSERT INTO rate_limits (identifier, endpoint, attempts, window_start) 
                VALUES (:identifier, :endpoint, 1, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
        $stmt->bindParam(':endpoint', $endpoint, PDO::PARAM_STR);
        $stmt->execute();
    }

    return true;
}

function getClientIdentifier(): string {
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function rateLimitExceededResponse(string $endpoint): void {
    http_response_code(429);
    if ($endpoint === 'global') {
        echo "<p>Trop de requêtes. Veuillez patienter avant de réessayer.</p>";
    } else {
        echo "<p>Trop de tentatives pour {$endpoint}. Réessayez dans 15 minutes.</p>";
    }
    exit;
}
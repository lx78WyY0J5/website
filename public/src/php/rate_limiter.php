<?php

function checkRateLimit(PDO $pdo, string $identifier, string $endpoint, int $maxAttempts, int $windowMinutes): bool {
    $windowStart = date('Y-m-d H:i:s', strtotime("-{$windowMinutes} minutes"));

    // Clean old entries (older than window)
    $cleanSql = "DELETE FROM rate_limits WHERE requested_at < :window_start";
    $cleanStmt = $pdo->prepare($cleanSql);
    $cleanStmt->bindParam(':window_start', $windowStart, PDO::PARAM_STR);
    $cleanStmt->execute();

    // Count requests in current window
    $countSql = "SELECT COUNT(*) as count FROM rate_limits 
                 WHERE identifier = :identifier AND endpoint = :endpoint 
                 AND requested_at >= :window_start";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
    $countStmt->bindParam(':endpoint', $endpoint, PDO::PARAM_STR);
    $countStmt->bindParam(':window_start', $windowStart, PDO::PARAM_STR);
    $countStmt->execute();

    $row = $countStmt->fetch(PDO::FETCH_ASSOC);
    $currentCount = (int)($row['count'] ?? 0);

    if ($currentCount >= $maxAttempts) {
        return false;
    }

    // Insert this request
    $insertSql = "INSERT INTO rate_limits (identifier, endpoint, requested_at) 
                  VALUES (:identifier, :endpoint, NOW())";
    $insertStmt = $pdo->prepare($insertSql);
    $insertStmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
    $insertStmt->bindParam(':endpoint', $endpoint, PDO::PARAM_STR);
    $insertStmt->execute();

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
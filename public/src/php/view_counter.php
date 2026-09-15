<?php

function incrementSiteViews(PDO $pdo): void {
    $stmt = $pdo->prepare("UPDATE `site_views` SET `total_views` = `total_views` + 1 WHERE `id` = 1");
    $stmt->execute();
}

function incrementPageViews(PDO $pdo, string $url): void {
    $stmt = $pdo->prepare("
        INSERT INTO `page_views` (`url`, `view_count`) VALUES (?, 1)
        ON DUPLICATE KEY UPDATE `view_count` = `view_count` + 1
    ");
    $stmt->execute([$url]);
}

function incrementUserPageViews(PDO $pdo, int $userId, string $url): void {
    $stmt = $pdo->prepare("
        INSERT INTO `user_page_views` (`user_id`, `url`, `view_count`) VALUES (?, ?, 1)
        ON DUPLICATE KEY UPDATE `view_count` = `view_count` + 1
    ");
    $stmt->execute([$userId, $url]);
}

function incrementUserTotalViews(PDO $pdo, int $userId): void {
    $stmt = $pdo->prepare("
        INSERT INTO `user_total_views` (`user_id`, `total_views`) VALUES (?, 1)
        ON DUPLICATE KEY UPDATE `total_views` = `total_views` + 1
    ");
    $stmt->execute([$userId]);
}

function getSiteViews(PDO $pdo): int {
    $stmt = $pdo->query("SELECT `total_views` FROM `site_views` WHERE `id` = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? (int)$result['total_views'] : 0;
}

function getPageViews(PDO $pdo, string $url): int {
    $stmt = $pdo->prepare("SELECT `view_count` FROM `page_views` WHERE `url` = ?");
    $stmt->execute([$url]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? (int)$result['view_count'] : 0;
}

function getUserPageViews(PDO $pdo, int $userId, string $url): int {
    $stmt = $pdo->prepare("SELECT `view_count` FROM `user_page_views` WHERE `user_id` = ? AND `url` = ?");
    $stmt->execute([$userId, $url]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? (int)$result['view_count'] : 0;
}

function getUserTotalViews(PDO $pdo, int $userId): int {
    $stmt = $pdo->prepare("SELECT `total_views` FROM `user_total_views` WHERE `user_id` = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? (int)$result['total_views'] : 0;
}

function trackView(PDO $pdo, string $url, ?int $userId = null): void {
    incrementSiteViews($pdo);
    incrementPageViews($pdo, $url);

    if ($userId !== null) {
        incrementUserPageViews($pdo, $userId, $url);
        incrementUserTotalViews($pdo, $userId);
    }
}

function getViewStats(PDO $pdo, string $url, ?int $userId = null): array {
    $stats = [
        'site_total' => getSiteViews($pdo),
        'page_total' => getPageViews($pdo, $url),
    ];

    if ($userId !== null) {
        $stats['user_page'] = getUserPageViews($pdo, $userId, $url);
        $stats['user_total'] = getUserTotalViews($pdo, $userId);
    }

    return $stats;
}
<?php
// Fusion d'un compte Discord seul dans un compte classique.
// Les données rattachées à user_id sont réaffectées avant la suppression de la ligne source.
// Les photos sont copiées, puis les fichiers source sont supprimés uniquement après le commit SQL.

function userMergeEarliestDate($firstDate, $secondDate)
{
    if (empty($firstDate) && empty($secondDate)) {
        return null;
    }

    if (empty($firstDate)) {
        return $secondDate;
    }

    if (empty($secondDate)) {
        return $firstDate;
    }

    $firstTimestamp = strtotime((string)$firstDate);
    $secondTimestamp = strtotime((string)$secondDate);

    if ($firstTimestamp === false) {
        return $secondDate;
    }

    if ($secondTimestamp === false) {
        return $firstDate;
    }

    if ($firstTimestamp <= $secondTimestamp) {
        return $firstDate;
    }

    return $secondDate;
}

function userMergeBuildUniqueFilename(string $directory, string $filename, array $reservedFilenames = []): string
{
    $safeFilename = basename($filename);
    $targetPath = $directory . "/" . $safeFilename;

    if (!file_exists($targetPath) && !in_array($safeFilename, $reservedFilenames, true)) {
        return $safeFilename;
    }

    $extension = pathinfo($safeFilename, PATHINFO_EXTENSION);
    $name = pathinfo($safeFilename, PATHINFO_FILENAME);
    $counter = 1;

    while (true) {
        if ($extension !== "") {
            $candidate = $name . "_" . $counter . "." . $extension;
        }
        else {
            $candidate = $name . "_" . $counter;
        }

        $candidatePath = $directory . "/" . $candidate;
        if (!file_exists($candidatePath) && !in_array($candidate, $reservedFilenames, true)) {
            return $candidate;
        }

        $counter = $counter + 1;
    }
}

function userMergeRemoveFiles(array $files): void
{
    foreach ($files as $file) {
        if (is_file($file)) {
            if (!unlink($file)) {
                error_log("Impossible de supprimer le fichier de fusion : " . $file);
            }
        }
    }
}

function userMergeGetReservedFilenames(PDO $pdo, int $targetUserId): array
{
    $sql = "SELECT filename FROM user_pictures WHERE user_id = :target_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":target_id", $targetUserId, PDO::PARAM_INT);
    $stmt->execute();

    $filenames = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $filenames[] = (string)$row["filename"];
    }

    return $filenames;
}

function userMergeCopyProfilePictures(int $sourceUserId, int $targetUserId, array $reservedFilenames = []): array
{
    $result = [
        "file_mappings" => [],
        "copied_source_files" => [],
        "copied_target_files" => [],
    ];

    $documentRoot = "";
    if (isset($_SERVER["DOCUMENT_ROOT"])) {
        $documentRoot = (string)$_SERVER["DOCUMENT_ROOT"];
    }

    if ($documentRoot === "") {
        throw new RuntimeException("Document root introuvable pour la fusion des photos.");
    }

    $sourceDirectory = $documentRoot . "/uploads/profile_pictures/" . $sourceUserId;
    $targetDirectory = $documentRoot . "/uploads/profile_pictures/" . $targetUserId;

    if (!is_dir($sourceDirectory)) {
        if (file_exists($sourceDirectory)) {
            throw new RuntimeException("Le chemin du dossier de photos source est occupé.");
        }
        return $result;
    }

    if (!is_dir($targetDirectory)) {
        if (file_exists($targetDirectory)) {
            throw new RuntimeException("Le chemin du dossier de photos cible est occupé.");
        }
        if (!mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
            throw new RuntimeException("Impossible de créer le dossier de photos cible.");
        }
    }

    $entries = scandir($sourceDirectory);
    if ($entries === false) {
        throw new RuntimeException("Impossible de lire le dossier de photos source.");
    }

    try {
        foreach ($entries as $entry) {
            if ($entry === "." || $entry === "..") {
                continue;
            }

            $sourcePath = $sourceDirectory . "/" . $entry;
            if (!is_file($sourcePath)) {
                continue;
            }

            $targetFilename = userMergeBuildUniqueFilename($targetDirectory, $entry, $reservedFilenames);
            $targetPath = $targetDirectory . "/" . $targetFilename;

            if (!copy($sourcePath, $targetPath)) {
                throw new RuntimeException("Impossible de copier une photo de profil.");
            }

            $oldRelativePath = "uploads/profile_pictures/" . $sourceUserId . "/" . $entry;
            $newRelativePath = "uploads/profile_pictures/" . $targetUserId . "/" . $targetFilename;

            $result["file_mappings"][$oldRelativePath] = $newRelativePath;
            $result["copied_source_files"][] = $sourcePath;
            $result["copied_target_files"][] = $targetPath;
        }
    }
    catch (Throwable $e) {
        userMergeRemoveFiles($result["copied_target_files"]);
        throw $e;
    }

    return $result;
}

function userMergeCleanupSourcePictures(int $sourceUserId, array $sourceFiles): void
{
    userMergeRemoveFiles($sourceFiles);

    $documentRoot = "";
    if (isset($_SERVER["DOCUMENT_ROOT"])) {
        $documentRoot = (string)$_SERVER["DOCUMENT_ROOT"];
    }

    if ($documentRoot === "") {
        return;
    }

    $sourceDirectory = $documentRoot . "/uploads/profile_pictures/" . $sourceUserId;
    if (is_dir($sourceDirectory)) {
        if (!rmdir($sourceDirectory)) {
            error_log("Le dossier de photos source n'est pas vide après fusion : " . $sourceDirectory);
        }
    }
}

function userMergeGetUsersForUpdate(PDO $pdo, int $sourceUserId, int $targetUserId): array
{
    $sql = "SELECT id, username, password, discord_id, discord_linked_at, created_at
        FROM users
        WHERE id = :source_id OR id = :target_id
        FOR UPDATE";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":source_id", $sourceUserId, PDO::PARAM_INT);
    $stmt->bindValue(":target_id", $targetUserId, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $sourceUser = null;
    $targetUser = null;

    foreach ($rows as $row) {
        if ((int)$row["id"] === $sourceUserId) {
            $sourceUser = $row;
        }
        if ((int)$row["id"] === $targetUserId) {
            $targetUser = $row;
        }
    }

    if ($sourceUser === null || $targetUser === null) {
        throw new RuntimeException("Compte source ou cible introuvable pendant la fusion.");
    }

    return [
        "source" => $sourceUser,
        "target" => $targetUser,
    ];
}

function userMergeTotalViews(PDO $pdo, string $table, int $sourceUserId, int $targetUserId): void
{
    $allowedTables = ["user_total_views", "user_api_total_views"];
    if (!in_array($table, $allowedTables, true)) {
        throw new RuntimeException("Table de vues totales non autorisée.");
    }

    $sql = "INSERT INTO `" . $table . "` (`user_id`, `total_views`)
        SELECT :target_id, `source`.`total_views`
        FROM `" . $table . "` AS `source`
        WHERE `source`.`user_id` = :source_id
        ON DUPLICATE KEY UPDATE `total_views` = `" . $table . "`.`total_views` + VALUES(`total_views`)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":target_id", $targetUserId, PDO::PARAM_INT);
    $stmt->bindValue(":source_id", $sourceUserId, PDO::PARAM_INT);
    $stmt->execute();

    $sql = "DELETE FROM `" . $table . "` WHERE `user_id` = :source_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":source_id", $sourceUserId, PDO::PARAM_INT);
    $stmt->execute();
}

function userMergePageViews(PDO $pdo, string $table, int $sourceUserId, int $targetUserId): void
{
    $allowedTables = ["user_page_views", "user_api_page_views"];
    if (!in_array($table, $allowedTables, true)) {
        throw new RuntimeException("Table de vues par page non autorisée.");
    }

    $sql = "INSERT INTO `" . $table . "` (`user_id`, `url`, `view_count`)
        SELECT :target_id, `source`.`url`, `source`.`view_count`
        FROM `" . $table . "` AS `source`
        WHERE `source`.`user_id` = :source_id
        ON DUPLICATE KEY UPDATE `view_count` = `" . $table . "`.`view_count` + VALUES(`view_count`)";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":target_id", $targetUserId, PDO::PARAM_INT);
    $stmt->bindValue(":source_id", $sourceUserId, PDO::PARAM_INT);
    $stmt->execute();

    $sql = "DELETE FROM `" . $table . "` WHERE `user_id` = :source_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":source_id", $sourceUserId, PDO::PARAM_INT);
    $stmt->execute();
}

function userMergeRateLimits(PDO $pdo, int $sourceUserId, int $targetUserId): void
{
    $sql = "UPDATE rate_limits SET identifier = :target_identifier WHERE identifier = :source_identifier";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":target_identifier", "user_" . $targetUserId, PDO::PARAM_STR);
    $stmt->bindValue(":source_identifier", "user_" . $sourceUserId, PDO::PARAM_STR);
    $stmt->execute();
}

function userMergeAssertNoSourceData(PDO $pdo, int $sourceUserId): void
{
    $tables = [
        "user_pictures",
        "user_page_views",
        "user_total_views",
        "user_api_page_views",
        "user_api_total_views",
    ];

    foreach ($tables as $table) {
        $sql = "SELECT COUNT(*) FROM `" . $table . "` WHERE `user_id` = :source_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":source_id", $sourceUserId, PDO::PARAM_INT);
        $stmt->execute();

        if ((int)$stmt->fetchColumn() !== 0) {
            throw new RuntimeException("Des données du compte source n'ont pas pu être réaffectées.");
        }
    }

    $sql = "SELECT COUNT(*) FROM rate_limits WHERE identifier = :source_identifier";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":source_identifier", "user_" . $sourceUserId, PDO::PARAM_STR);
    $stmt->execute();

    if ((int)$stmt->fetchColumn() !== 0) {
        throw new RuntimeException("Des données de rate limiting du compte source n'ont pas pu être réaffectées.");
    }
}

function userMergeProfilePictures(PDO $pdo, int $sourceUserId, int $targetUserId, array $fileMappings): void
{
    $sql = "UPDATE user_pictures SET user_id = :target_id WHERE user_id = :source_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":target_id", $targetUserId, PDO::PARAM_INT);
    $stmt->bindValue(":source_id", $sourceUserId, PDO::PARAM_INT);
    $stmt->execute();

    $sql = "UPDATE user_pictures
        SET file_path = :new_path, filename = :new_filename
        WHERE user_id = :target_id AND file_path = :old_path";

    $stmt = $pdo->prepare($sql);

    foreach ($fileMappings as $oldPath => $newPath) {
        $stmt->bindValue(":new_path", $newPath, PDO::PARAM_STR);
        $stmt->bindValue(":new_filename", basename($newPath), PDO::PARAM_STR);
        $stmt->bindValue(":target_id", $targetUserId, PDO::PARAM_INT);
        $stmt->bindValue(":old_path", $oldPath, PDO::PARAM_STR);
        $stmt->execute();
    }
}

function mergeUserAccounts(PDO $pdo, int $sourceUserId, int $targetUserId, string $discordId): void
{
    if ($sourceUserId <= 0 || $targetUserId <= 0) {
        throw new RuntimeException("Identifiant de compte invalide pour la fusion.");
    }

    if ($sourceUserId === $targetUserId) {
        throw new RuntimeException("Impossible de fusionner un compte avec lui-même.");
    }

    $pictureMerge = [
        "file_mappings" => [],
        "copied_source_files" => [],
        "copied_target_files" => [],
    ];

    try {
        if (!$pdo->beginTransaction()) {
            throw new RuntimeException("Impossible de démarrer la transaction de fusion.");
        }

        $users = userMergeGetUsersForUpdate($pdo, $sourceUserId, $targetUserId);
        $sourceUser = $users["source"];
        $targetUser = $users["target"];

        if ($sourceUser["username"] !== null && $sourceUser["username"] !== "") {
            throw new RuntimeException("Le compte source n'est pas un compte Discord seul.");
        }

        if ($targetUser["username"] === null || $targetUser["username"] === "") {
            throw new RuntimeException("Le compte cible n'est pas un compte classique.");
        }

        if (empty($sourceUser["discord_id"])) {
            throw new RuntimeException("Le compte source n'est pas lié à Discord.");
        }

        if ((string)$sourceUser["discord_id"] !== $discordId) {
            throw new RuntimeException("L'identifiant Discord ne correspond pas au compte source.");
        }

        $targetDiscordId = "";
        if (!empty($targetUser["discord_id"])) {
            $targetDiscordId = (string)$targetUser["discord_id"];
        }

        if ($targetDiscordId !== "" && $targetDiscordId !== $discordId) {
            throw new RuntimeException("Le compte cible est déjà lié à un autre compte Discord.");
        }

        $reservedFilenames = userMergeGetReservedFilenames($pdo, $targetUserId);
        $pictureMerge = userMergeCopyProfilePictures($sourceUserId, $targetUserId, $reservedFilenames);

        userMergeProfilePictures($pdo, $sourceUserId, $targetUserId, $pictureMerge["file_mappings"]);
        userMergeTotalViews($pdo, "user_total_views", $sourceUserId, $targetUserId);
        userMergeTotalViews($pdo, "user_api_total_views", $sourceUserId, $targetUserId);
        userMergePageViews($pdo, "user_page_views", $sourceUserId, $targetUserId);
        userMergePageViews($pdo, "user_api_page_views", $sourceUserId, $targetUserId);
        userMergeRateLimits($pdo, $sourceUserId, $targetUserId);

        $createdAt = userMergeEarliestDate($targetUser["created_at"], $sourceUser["created_at"]);
        $discordLinkedAt = userMergeEarliestDate($targetUser["discord_linked_at"], $sourceUser["discord_linked_at"]);

        $sql = "UPDATE users SET discord_id = NULL, discord_linked_at = NULL WHERE id = :source_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":source_id", $sourceUserId, PDO::PARAM_INT);
        $stmt->execute();

        $sql = "UPDATE users
            SET discord_id = :discord_id,
                created_at = COALESCE(:created_at, NOW()),
                discord_linked_at = COALESCE(:discord_linked_at, NOW())
            WHERE id = :target_id AND discord_id IS NULL";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":discord_id", $discordId, PDO::PARAM_STR);
        $stmt->bindValue(":target_id", $targetUserId, PDO::PARAM_INT);

        if ($createdAt === null) {
            $stmt->bindValue(":created_at", null, PDO::PARAM_NULL);
        }
        else {
            $stmt->bindValue(":created_at", $createdAt, PDO::PARAM_STR);
        }

        if ($discordLinkedAt === null) {
            $stmt->bindValue(":discord_linked_at", null, PDO::PARAM_NULL);
        }
        else {
            $stmt->bindValue(":discord_linked_at", $discordLinkedAt, PDO::PARAM_STR);
        }

        $stmt->execute();

        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException("Le compte cible a été lié entre-temps.");
        }

        // Sécurité : aucune donnée ne doit encore dépendre du compte source avant
        // la suppression en cascade.
        userMergeAssertNoSourceData($pdo, $sourceUserId);

        $sql = "DELETE FROM users WHERE id = :source_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":source_id", $sourceUserId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException("Le compte source n'a pas pu être supprimé.");
        }

        $pdo->commit();
    }
    catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        userMergeRemoveFiles($pictureMerge["copied_target_files"]);
        throw $e;
    }

    userMergeCleanupSourcePictures($sourceUserId, $pictureMerge["copied_source_files"]);
}

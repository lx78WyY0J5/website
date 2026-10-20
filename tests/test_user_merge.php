<?php
// Tests d'intégration de la fusion compte Discord-only -> compte classique.
// Les données de test sont créées avec des identifiants uniques puis nettoyées
// automatiquement, même en cas d'échec.

error_reporting(E_ALL);
ini_set("display_errors", "1");

$documentRoot = realpath(__DIR__ . "/../public");
if ($documentRoot === false) {
    fwrite(STDERR, "Dossier public introuvable.\n");
    exit(1);
}

$_SERVER["DOCUMENT_ROOT"] = $documentRoot;

require_once $documentRoot . "/src/php/loadEnv.php";
require_once $documentRoot . "/src/php/PDO.php";
require_once $documentRoot . "/src/php/discord_oauth.php";
require_once $documentRoot . "/src/php/user_merge.php";

if (!isset($pdo) || !($pdo instanceof PDO)) {
    fwrite(STDERR, "Connexion PDO indisponible.\n");
    exit(1);
}

$GLOBALS["testAssertions"] = 0;
$GLOBALS["testCreatedUserIds"] = [];
$GLOBALS["testCreatedPaths"] = [];
$GLOBALS["testCreatedTriggers"] = [];
$GLOBALS["testRateEndpoints"] = [];
$GLOBALS["testCurrentScenario"] = "initialisation";

register_shutdown_function("testCleanup");

function testAssertTrue($condition, string $message): void
{
    $GLOBALS["testAssertions"] = $GLOBALS["testAssertions"] + 1;

    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function testAssertSame($expected, $actual, string $message): void
{
    $GLOBALS["testAssertions"] = $GLOBALS["testAssertions"] + 1;

    if ($expected !== $actual) {
        throw new RuntimeException(
            $message
            . " | attendu=" . testExport($expected)
            . " | obtenu=" . testExport($actual)
        );
    }
}

function testAssertNotNull($value, string $message): void
{
    testAssertTrue($value !== null, $message);
}

function testExport($value): string
{
    if ($value === null) {
        return "NULL";
    }

    if (is_bool($value)) {
        if ($value) {
            return "true";
        }
        return "false";
    }

    if (is_array($value)) {
        $encoded = json_encode($value);
        if ($encoded === false) {
            return "array";
        }
        return $encoded;
    }

    return (string)$value;
}

function testAssertThrows(
    string $expectedClass,
    string $callableName,
    array $arguments,
    string $message,
    string $expectedMessagePart = ""
): Throwable {
    $thrown = null;

    try {
        call_user_func_array($callableName, $arguments);
    }
    catch (Throwable $e) {
        $thrown = $e;
    }

    testAssertTrue($thrown !== null, $message . " (aucune exception)");
    testAssertTrue(
        $thrown instanceof $expectedClass,
        $message . " (classe obtenue : " . get_class($thrown) . ")"
    );

    if ($expectedMessagePart !== "") {
        testAssertTrue(
            strpos($thrown->getMessage(), $expectedMessagePart) !== false,
            $message . " (message : " . $thrown->getMessage() . ")"
        );
    }

    return $thrown;
}

function testNewSuffix(): string
{
    return bin2hex(random_bytes(6));
}

function testNewDiscordId(PDO $pdo): string
{
    while (true) {
        $candidate = (string)random_int(700000000000000000, 899999999999999999);
        $sql = "SELECT id FROM users WHERE discord_id = :discord_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":discord_id", $candidate, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->fetchColumn() === false) {
            return $candidate;
        }
    }
}

function testRegisterUser(int $userId): void
{
    $GLOBALS["testCreatedUserIds"][] = $userId;
    $GLOBALS["testCreatedPaths"][] = testProfileDirectory($userId);
}

function testProfileDirectory(int $userId): string
{
    return (string)$_SERVER["DOCUMENT_ROOT"] . "/uploads/profile_pictures/" . $userId;
}

function testCreateUser(
    PDO $pdo,
    ?string $username,
    ?string $password,
    ?string $discordId,
    ?string $createdAt,
    ?string $linkedAt
): int {
    $sql = "INSERT INTO users
        (username, password, discord_id, discord_linked_at, created_at)
        VALUES
        (:username, :password, :discord_id, :discord_linked_at, :created_at)";

    $stmt = $pdo->prepare($sql);

    if ($username === null) {
        $stmt->bindValue(":username", null, PDO::PARAM_NULL);
    }
    else {
        $stmt->bindValue(":username", $username, PDO::PARAM_STR);
    }

    if ($password === null) {
        $stmt->bindValue(":password", null, PDO::PARAM_NULL);
    }
    else {
        $stmt->bindValue(":password", $password, PDO::PARAM_STR);
    }

    if ($discordId === null) {
        $stmt->bindValue(":discord_id", null, PDO::PARAM_NULL);
    }
    else {
        $stmt->bindValue(":discord_id", $discordId, PDO::PARAM_STR);
    }

    if ($linkedAt === null) {
        $stmt->bindValue(":discord_linked_at", null, PDO::PARAM_NULL);
    }
    else {
        $stmt->bindValue(":discord_linked_at", $linkedAt, PDO::PARAM_STR);
    }

    if ($createdAt === null) {
        $stmt->bindValue(":created_at", null, PDO::PARAM_NULL);
    }
    else {
        $stmt->bindValue(":created_at", $createdAt, PDO::PARAM_STR);
    }

    $stmt->execute();

    $userId = (int)$pdo->lastInsertId();
    if ($userId <= 0) {
        throw new RuntimeException("Identifiant utilisateur invalide après insertion.");
    }

    testRegisterUser($userId);
    return $userId;
}

function testCreateDiscordOnlyUser(
    PDO $pdo,
    ?string $createdAt = null,
    ?string $linkedAt = null,
    ?string $username = null
): array {
    $discordId = testNewDiscordId($pdo);
    $userId = testCreateUser($pdo, $username, null, $discordId, $createdAt, $linkedAt);

    return [
        "id" => $userId,
        "discord_id" => $discordId,
    ];
}

function testCreateClassicUser(
    PDO $pdo,
    ?string $createdAt = null,
    ?string $linkedAt = null,
    ?string $discordId = null,
    ?string $username = null
): array {
    if ($username === null) {
        $username = "merge_test_" . testNewSuffix();
    }

    $password = password_hash("TestPassword1!", PASSWORD_DEFAULT);
    if ($password === false) {
        throw new RuntimeException("Impossible de générer le mot de passe de test.");
    }

    $userId = testCreateUser($pdo, $username, $password, $discordId, $createdAt, $linkedAt);

    return [
        "id" => $userId,
        "username" => $username,
        "password" => $password,
        "discord_id" => $discordId,
    ];
}

function testGetUser(PDO $pdo, int $userId): ?array
{
    $sql = "SELECT id, username, password, discord_id, discord_linked_at, created_at
        FROM users
        WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":id", $userId, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row === false) {
        return null;
    }

    return $row;
}

function testSetDiscordId(PDO $pdo, int $userId, ?string $discordId, ?string $linkedAt): void
{
    $sql = "UPDATE users SET discord_id = :discord_id, discord_linked_at = :linked_at WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    if ($discordId === null) {
        $stmt->bindValue(":discord_id", null, PDO::PARAM_NULL);
    }
    else {
        $stmt->bindValue(":discord_id", $discordId, PDO::PARAM_STR);
    }

    if ($linkedAt === null) {
        $stmt->bindValue(":linked_at", null, PDO::PARAM_NULL);
    }
    else {
        $stmt->bindValue(":linked_at", $linkedAt, PDO::PARAM_STR);
    }

    $stmt->bindValue(":id", $userId, PDO::PARAM_INT);
    $stmt->execute();
}

function testCountRows(PDO $pdo, string $table, string $where, array $parameters): int
{
    $sql = "SELECT COUNT(*) FROM `" . $table . "` WHERE " . $where;
    $stmt = $pdo->prepare($sql);

    $position = 1;
    foreach ($parameters as $parameter) {
        if (is_int($parameter)) {
            $stmt->bindValue($position, $parameter, PDO::PARAM_INT);
        }
        elseif ($parameter === null) {
            $stmt->bindValue($position, null, PDO::PARAM_NULL);
        }
        else {
            $stmt->bindValue($position, (string)$parameter, PDO::PARAM_STR);
        }
        $position = $position + 1;
    }

    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function testGetTotalViews(PDO $pdo, string $table, int $userId): ?int
{
    $sql = "SELECT total_views FROM `" . $table . "` WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);
    $stmt->execute();

    $value = $stmt->fetchColumn();
    if ($value === false) {
        return null;
    }

    return (int)$value;
}

function testGetPageViews(PDO $pdo, string $table, int $userId): array
{
    $sql = "SELECT url, view_count FROM `" . $table . "` WHERE user_id = :user_id ORDER BY url";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);
    $stmt->execute();

    $result = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $result[(string)$row["url"]] = (int)$row["view_count"];
    }

    return $result;
}

function testSetTotalViews(PDO $pdo, string $table, int $userId, int $totalViews): void
{
    $allowedTables = ["user_total_views", "user_api_total_views"];
    if (!in_array($table, $allowedTables, true)) {
        throw new RuntimeException("Table de vues totales non autorisée dans le test.");
    }

    $sql = "INSERT INTO `" . $table . "` (user_id, total_views)
        VALUES (:user_id, :total_views)
        ON DUPLICATE KEY UPDATE total_views = VALUES(total_views)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);
    $stmt->bindValue(":total_views", $totalViews, PDO::PARAM_INT);
    $stmt->execute();
}

function testSetPageViews(
    PDO $pdo,
    string $table,
    int $userId,
    string $url,
    int $viewCount
): void {
    $allowedTables = ["user_page_views", "user_api_page_views"];
    if (!in_array($table, $allowedTables, true)) {
        throw new RuntimeException("Table de vues par page non autorisée dans le test.");
    }

    $sql = "INSERT INTO `" . $table . "` (user_id, url, view_count)
        VALUES (:user_id, :url, :view_count)
        ON DUPLICATE KEY UPDATE view_count = VALUES(view_count)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);
    $stmt->bindValue(":url", $url, PDO::PARAM_STR);
    $stmt->bindValue(":view_count", $viewCount, PDO::PARAM_INT);
    $stmt->execute();
}

function testCreateProfileFile(int $userId, string $filename, string $content): string
{
    $directory = testProfileDirectory($userId);

    if (!is_dir($directory)) {
        if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException("Impossible de créer le dossier de test : " . $directory);
        }
    }

    $path = $directory . "/" . $filename;
    if (file_put_contents($path, $content) === false) {
        throw new RuntimeException("Impossible d'écrire le fichier de test : " . $path);
    }

    return "uploads/profile_pictures/" . $userId . "/" . $filename;
}

function testCreateProfilePathAsFile(int $userId, string $content): string
{
    $path = testProfileDirectory($userId);
    $parent = dirname($path);

    if (!is_dir($parent)) {
        if (!mkdir($parent, 0755, true) && !is_dir($parent)) {
            throw new RuntimeException("Impossible de créer le dossier parent de test.");
        }
    }

    if (file_put_contents($path, $content) === false) {
        throw new RuntimeException("Impossible de créer le fichier au chemin du dossier de photos.");
    }

    $GLOBALS["testCreatedPaths"][] = $path;
    return $path;
}

function testInsertPicture(
    PDO $pdo,
    int $userId,
    string $filename,
    ?string $relativePath = null
): int {
    if ($relativePath === null) {
        $relativePath = "uploads/profile_pictures/" . $userId . "/" . $filename;
    }

    $sql = "INSERT INTO user_pictures (user_id, filename, file_path)
        VALUES (:user_id, :filename, :file_path)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);
    $stmt->bindValue(":filename", $filename, PDO::PARAM_STR);
    $stmt->bindValue(":file_path", $relativePath, PDO::PARAM_STR);
    $stmt->execute();

    return (int)$pdo->lastInsertId();
}

function testGetPictureByFilename(PDO $pdo, int $userId, string $filename): ?array
{
    $sql = "SELECT id, user_id, filename, file_path
        FROM user_pictures
        WHERE user_id = :user_id AND filename = :filename";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);
    $stmt->bindValue(":filename", $filename, PDO::PARAM_STR);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row === false) {
        return null;
    }

    return $row;
}

function testRegisterRateEndpoint(string $endpoint): void
{
    $GLOBALS["testRateEndpoints"][] = $endpoint;
}

function testInsertRateLimit(
    PDO $pdo,
    string $identifier,
    string $endpoint,
    ?string $requestedAt = null
): void {
    testRegisterRateEndpoint($endpoint);

    if ($requestedAt === null) {
        $sql = "INSERT INTO rate_limits (identifier, endpoint) VALUES (:identifier, :endpoint)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":identifier", $identifier, PDO::PARAM_STR);
        $stmt->bindValue(":endpoint", $endpoint, PDO::PARAM_STR);
        $stmt->execute();
        return;
    }

    $sql = "INSERT INTO rate_limits (identifier, endpoint, requested_at)
        VALUES (:identifier, :endpoint, :requested_at)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":identifier", $identifier, PDO::PARAM_STR);
    $stmt->bindValue(":endpoint", $endpoint, PDO::PARAM_STR);
    $stmt->bindValue(":requested_at", $requestedAt, PDO::PARAM_STR);
    $stmt->execute();
}

function testGetRateLimitCount(PDO $pdo, string $identifier, string $endpoint): int
{
    return testCountRows(
        $pdo,
        "rate_limits",
        "identifier = ? AND endpoint = ?",
        [$identifier, $endpoint]
    );
}

function testCreateTrigger(PDO $pdo, string $name, string $sql): void
{
    $GLOBALS["testCreatedTriggers"][] = $name;
    $pdo->exec($sql);
}

function testDropTrigger(PDO $pdo, string $name): void
{
    $pdo->exec("DROP TRIGGER IF EXISTS `" . $name . "`");

    $remaining = [];
    foreach ($GLOBALS["testCreatedTriggers"] as $triggerName) {
        if ($triggerName !== $name) {
            $remaining[] = $triggerName;
        }
    }
    $GLOBALS["testCreatedTriggers"] = $remaining;
}

function testRemoveDirectory(string $directory): void
{
    if (!is_dir($directory)) {
        return;
    }

    $entries = scandir($directory);
    if ($entries === false) {
        return;
    }

    foreach ($entries as $entry) {
        if ($entry === "." || $entry === "..") {
            continue;
        }

        $path = $directory . "/" . $entry;
        if (is_dir($path) && !is_link($path)) {
            testRemoveDirectory($path);
        }
        else {
            if (!unlink($path)) {
                error_log("Nettoyage impossible : " . $path);
            }
        }
    }

    if (!rmdir($directory)) {
        error_log("Nettoyage du dossier impossible : " . $directory);
    }
}

function testRemovePath(string $path): void
{
    if (is_dir($path) && !is_link($path)) {
        testRemoveDirectory($path);
        return;
    }

    if (is_file($path) || is_link($path)) {
        if (!unlink($path)) {
            error_log("Nettoyage du fichier impossible : " . $path);
        }
    }
}

function testCleanup(): void
{
    global $pdo;

    if (isset($pdo) && $pdo instanceof PDO) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        foreach ($GLOBALS["testCreatedTriggers"] as $triggerName) {
            try {
                $pdo->exec("DROP TRIGGER IF EXISTS `" . $triggerName . "`");
            }
            catch (Throwable $e) {
                error_log("Suppression du trigger impossible : " . $triggerName);
            }
        }
        $GLOBALS["testCreatedTriggers"] = [];

        foreach ($GLOBALS["testCreatedUserIds"] as $userId) {
            try {
                $sql = "DELETE FROM rate_limits WHERE identifier = :identifier";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(":identifier", "user_" . $userId, PDO::PARAM_STR);
                $stmt->execute();
            }
            catch (Throwable $e) {
                error_log("Nettoyage rate_limits impossible pour user_" . $userId);
            }
        }

        foreach ($GLOBALS["testRateEndpoints"] as $endpoint) {
            try {
                $sql = "DELETE FROM rate_limits WHERE endpoint = :endpoint";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(":endpoint", $endpoint, PDO::PARAM_STR);
                $stmt->execute();
            }
            catch (Throwable $e) {
                error_log("Nettoyage rate_limits impossible pour " . $endpoint);
            }
        }

        foreach ($GLOBALS["testCreatedUserIds"] as $userId) {
            try {
                $sql = "DELETE FROM users WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(":id", $userId, PDO::PARAM_INT);
                $stmt->execute();
            }
            catch (Throwable $e) {
                error_log("Nettoyage users impossible pour " . $userId);
            }
        }
    }

    foreach ($GLOBALS["testCreatedPaths"] as $path) {
        testRemovePath($path);
    }

    $GLOBALS["testCreatedUserIds"] = [];
    $GLOBALS["testCreatedPaths"] = [];
    $GLOBALS["testRateEndpoints"] = [];
}

function testScenarioDateHelpers(): void
{
    global $pdo;

    testAssertSame(null, userMergeEarliestDate(null, null), "Date : deux NULL");
    testAssertSame(
        "2020-01-01 00:00:00",
        userMergeEarliestDate(null, "2020-01-01 00:00:00"),
        "Date : premier NULL"
    );
    testAssertSame(
        "2020-01-01 00:00:00",
        userMergeEarliestDate("2020-01-01 00:00:00", null),
        "Date : second NULL"
    );
    testAssertSame(
        "2020-01-01 00:00:00",
        userMergeEarliestDate("2020-01-01 00:00:00", "2021-01-01 00:00:00"),
        "Date : première plus ancienne"
    );
    testAssertSame(
        "2020-01-01 00:00:00",
        userMergeEarliestDate("2021-01-01 00:00:00", "2020-01-01 00:00:00"),
        "Date : seconde plus ancienne"
    );
    testAssertSame(
        "2020-01-01 00:00:00",
        userMergeEarliestDate("2020-01-01 00:00:00", "2020-01-01 00:00:00"),
        "Date : dates identiques"
    );
    testAssertSame(
        "2020-01-01 00:00:00",
        userMergeEarliestDate("date-invalide", "2020-01-01 00:00:00"),
        "Date : première invalide"
    );
    testAssertSame(
        "2020-01-01 00:00:00",
        userMergeEarliestDate("2020-01-01 00:00:00", "date-invalide"),
        "Date : seconde invalide"
    );

    $cases = [
        [
            "source_created" => "2020-01-01 00:00:00",
            "source_linked" => "2020-01-02 00:00:00",
            "target_created" => "2022-01-01 00:00:00",
            "target_linked" => null,
            "expected_created" => "2020-01-01 00:00:00",
            "expected_linked" => "2020-01-02 00:00:00",
            "expect_now" => false,
        ],
        [
            "source_created" => "2024-01-01 00:00:00",
            "source_linked" => "2024-01-02 00:00:00",
            "target_created" => "2018-01-01 00:00:00",
            "target_linked" => "2017-01-01 00:00:00",
            "expected_created" => "2018-01-01 00:00:00",
            "expected_linked" => "2017-01-01 00:00:00",
            "expect_now" => false,
        ],
        [
            "source_created" => null,
            "source_linked" => "2021-01-01 00:00:00",
            "target_created" => "2019-01-01 00:00:00",
            "target_linked" => null,
            "expected_created" => "2019-01-01 00:00:00",
            "expected_linked" => "2021-01-01 00:00:00",
            "expect_now" => false,
        ],
        [
            "source_created" => null,
            "source_linked" => null,
            "target_created" => null,
            "target_linked" => null,
            "expected_created" => null,
            "expected_linked" => null,
            "expect_now" => true,
        ],
        [
            "source_created" => "2020-01-01 00:00:00",
            "source_linked" => "2021-01-01 00:00:00",
            "target_created" => "2022-01-01 00:00:00",
            "target_linked" => "2019-01-01 00:00:00",
            "expected_created" => "2020-01-01 00:00:00",
            "expected_linked" => "2019-01-01 00:00:00",
            "expect_now" => false,
        ],
    ];

    foreach ($cases as $case) {
        $source = testCreateDiscordOnlyUser(
            $pdo,
            $case["source_created"],
            $case["source_linked"]
        );
        $target = testCreateClassicUser(
            $pdo,
            $case["target_created"],
            $case["target_linked"]
        );

        mergeUserAccounts($pdo, $source["id"], $target["id"], $source["discord_id"]);

        $targetRow = testGetUser($pdo, $target["id"]);
        testAssertNotNull($targetRow, "Date : compte cible présent après fusion");
        testAssertSame(null, testGetUser($pdo, $source["id"]), "Date : compte source supprimé");

        if ($case["expect_now"] === true) {
            testAssertNotNull($targetRow["created_at"], "Date : created_at non NULL après fallback");
            testAssertNotNull($targetRow["discord_linked_at"], "Date : discord_linked_at non NULL après fallback");
        }
        else {
            testAssertSame(
                $case["expected_created"],
                $targetRow["created_at"],
                "Date : created_at attendu"
            );
            testAssertSame(
                $case["expected_linked"],
                $targetRow["discord_linked_at"],
                "Date : discord_linked_at attendu"
            );
        }
    }
}

function testScenarioFilenameHelper(): void
{
    $directory = sys_get_temp_dir() . "/user_merge_test_" . testNewSuffix();

    if (!mkdir($directory, 0755, true)) {
        throw new RuntimeException("Impossible de créer le dossier temporaire de test.");
    }

    try {
        testAssertSame(
            "photo.jpg",
            userMergeBuildUniqueFilename($directory, "photo.jpg"),
            "Fichier : nom libre"
        );
        file_put_contents($directory . "/photo.jpg", "x");
        testAssertSame(
            "photo_1.jpg",
            userMergeBuildUniqueFilename($directory, "photo.jpg"),
            "Fichier : première collision"
        );
        file_put_contents($directory . "/photo_1.jpg", "x");
        testAssertSame(
            "photo_2.jpg",
            userMergeBuildUniqueFilename($directory, "photo.jpg"),
            "Fichier : deuxième collision"
        );

        testAssertSame(
            "noext",
            userMergeBuildUniqueFilename($directory, "noext"),
            "Fichier : nom sans extension libre"
        );
        file_put_contents($directory . "/noext", "x");
        testAssertSame(
            "noext_1",
            userMergeBuildUniqueFilename($directory, "noext"),
            "Fichier : collision sans extension"
        );

        file_put_contents($directory . "/archive.tar.gz", "x");
        testAssertSame(
            "archive.tar_1.gz",
            userMergeBuildUniqueFilename($directory, "archive.tar.gz"),
            "Fichier : collision avec double extension"
        );

        testAssertSame(
            "reserved_1.jpg",
            userMergeBuildUniqueFilename($directory, "reserved.jpg", ["reserved.jpg"]),
            "Fichier : nom réservé en base sans fichier"
        );

        testAssertSame(
            "evil.jpg",
            userMergeBuildUniqueFilename($directory, "../../evil.jpg"),
            "Fichier : traversée de chemin neutralisée"
        );

        $temporaryFile = $directory . "/a-supprimer.txt";
        file_put_contents($temporaryFile, "x");
        userMergeRemoveFiles([$temporaryFile]);
        testAssertTrue(!file_exists($temporaryFile), "Fichier : suppression simple");
        userMergeRemoveFiles([$temporaryFile]);
        testAssertTrue(true, "Fichier : suppression d'un fichier absent sans erreur");
    }
    finally {
        testRemoveDirectory($directory);
    }
}

function testScenarioDiscordHelpers(): void
{
    global $pdo;

    $target = testCreateClassicUser($pdo);
    $missingId = 999999999;

    $row = discordFindUserById($pdo, $target["id"]);
    testAssertNotNull($row, "Discord : utilisateur trouvé par id");
    testAssertSame($target["id"], (int)$row["id"], "Discord : id attendu");
    testAssertSame($target["username"], $row["username"], "Discord : username attendu");

    testAssertSame(null, discordFindUserById($pdo, $missingId), "Discord : utilisateur absent par id");
    testAssertSame(null, discordFindUserByDiscordId($pdo, "123456789012345678"), "Discord : discord_id absent");

    $discordId = testNewDiscordId($pdo);
    $affectedRows = discordLinkToUser($pdo, $target["id"], $discordId);
    testAssertSame(1, $affectedRows, "Discord : premier lien accepté");

    $row = testGetUser($pdo, $target["id"]);
    testAssertSame($discordId, (string)$row["discord_id"], "Discord : discord_id enregistré");
    testAssertNotNull($row["discord_linked_at"], "Discord : date de liaison enregistrée");
    $linkedAt = $row["discord_linked_at"];

    $foundByDiscord = discordFindUserByDiscordId($pdo, $discordId);
    testAssertNotNull($foundByDiscord, "Discord : utilisateur trouvé par discord_id");
    testAssertSame($target["id"], (int)$foundByDiscord["id"], "Discord : id trouvé par discord_id");

    $affectedRows = discordLinkToUser($pdo, $target["id"], $discordId);
    testAssertSame(0, $affectedRows, "Discord : même lien idempotent");
    $row = testGetUser($pdo, $target["id"]);
    testAssertSame($linkedAt, $row["discord_linked_at"], "Discord : date de liaison conservée");

    $otherDiscordId = testNewDiscordId($pdo);
    $affectedRows = discordLinkToUser($pdo, $target["id"], $otherDiscordId);
    testAssertSame(0, $affectedRows, "Discord : second Discord refusé");
    $row = testGetUser($pdo, $target["id"]);
    testAssertSame($discordId, (string)$row["discord_id"], "Discord : premier Discord conservé");
    testAssertSame($linkedAt, $row["discord_linked_at"], "Discord : date de liaison inchangée après refus");

    testAssertSame(
        0,
        discordLinkToUser($pdo, $missingId, $otherDiscordId),
        "Discord : utilisateur absent ignoré"
    );
}

function testBuildNoCopyFixture(
    PDO $pdo,
    ?string $sourceUsername,
    ?string $sourceDiscordId,
    ?string $targetUsername,
    ?string $targetDiscordId,
    bool $targetClassic
): array {
    $sourcePassword = null;
    if ($sourceUsername !== null) {
        $sourcePassword = password_hash("TestPassword1!", PASSWORD_DEFAULT);
        if ($sourcePassword === false) {
            throw new RuntimeException("Impossible de générer le mot de passe source.");
        }
    }

    $sourceId = testCreateUser(
        $pdo,
        $sourceUsername,
        $sourcePassword,
        $sourceDiscordId,
        "2020-01-01 00:00:00",
        "2020-01-02 00:00:00"
    );

    if ($targetClassic === true) {
        $target = testCreateClassicUser(
            $pdo,
            "2022-01-01 00:00:00",
            null,
            $targetDiscordId,
            $targetUsername
        );
    }
    else {
        $targetId = testCreateUser(
            $pdo,
            $targetUsername,
            null,
            $targetDiscordId,
            "2022-01-01 00:00:00",
            null
        );
        $target = [
            "id" => $targetId,
            "username" => $targetUsername,
            "password" => null,
            "discord_id" => $targetDiscordId,
        ];
    }

    $relativePath = testCreateProfileFile($sourceId, "source_only.jpg", "source-only");
    testInsertPicture($pdo, $sourceId, "source_only.jpg", $relativePath);

    return [
        "source_id" => $sourceId,
        "source_username" => $sourceUsername,
        "source_password" => $sourcePassword,
        "source_discord_id" => $sourceDiscordId,
        "target" => $target,
        "source_file" => $relativePath,
    ];
}

function testAssertNoCopyFixtureUnchanged(PDO $pdo, array $fixture): void
{
    $sourceRow = testGetUser($pdo, $fixture["source_id"]);
    testAssertNotNull($sourceRow, "Validation : compte source toujours présent");
    testAssertSame(
        $fixture["source_username"],
        $sourceRow["username"],
        "Validation : username source inchangé"
    );
    testAssertSame(
        $fixture["source_password"],
        $sourceRow["password"],
        "Validation : mot de passe source inchangé"
    );

    $sourceDiscordId = null;
    if ($sourceRow["discord_id"] !== null) {
        $sourceDiscordId = (string)$sourceRow["discord_id"];
    }
    testAssertSame(
        $fixture["source_discord_id"],
        $sourceDiscordId,
        "Validation : discord_id source inchangé"
    );

    testAssertSame(
        1,
        testCountRows($pdo, "user_pictures", "user_id = ?", [$fixture["source_id"]]),
        "Validation : photo source toujours rattachée à la source"
    );
    testAssertSame(
        0,
        testCountRows($pdo, "user_pictures", "user_id = ?", [$fixture["target"]["id"]]),
        "Validation : aucune photo copiée vers la cible"
    );

    $sourceFileAbsolute = (string)$_SERVER["DOCUMENT_ROOT"] . "/" . $fixture["source_file"];
    testAssertTrue(is_file($sourceFileAbsolute), "Validation : fichier source toujours présent");

    $targetFileAbsolute = testProfileDirectory($fixture["target"]["id"]) . "/source_only.jpg";
    testAssertTrue(!file_exists($targetFileAbsolute), "Validation : aucun fichier copié vers la cible");

    $targetRow = testGetUser($pdo, $fixture["target"]["id"]);
    testAssertNotNull($targetRow, "Validation : compte cible toujours présent");
    testAssertSame(
        $fixture["target"]["username"],
        $targetRow["username"],
        "Validation : username cible inchangé"
    );
    testAssertSame(
        $fixture["target"]["password"],
        $targetRow["password"],
        "Validation : mot de passe cible inchangé"
    );

    $targetDiscordId = null;
    if ($targetRow["discord_id"] !== null) {
        $targetDiscordId = (string)$targetRow["discord_id"];
    }
    testAssertSame(
        $fixture["target"]["discord_id"],
        $targetDiscordId,
        "Validation : discord_id cible inchangé"
    );
}

function testScenarioValidation(): void
{
    global $pdo;

    $sourceClassicUsername = "classic_src_" . testNewSuffix();
    $fixture = testBuildNoCopyFixture(
        $pdo,
        $sourceClassicUsername,
        testNewDiscordId($pdo),
        null,
        null,
        true
    );
    testAssertThrows(
        "RuntimeException",
        "mergeUserAccounts",
        [$pdo, $fixture["source_id"], $fixture["target"]["id"], $fixture["source_discord_id"]],
        "Validation : source classique refusée",
        "compte source"
    );
    testAssertNoCopyFixtureUnchanged($pdo, $fixture);

    $fixture = testBuildNoCopyFixture(
        $pdo,
        null,
        testNewDiscordId($pdo),
        null,
        null,
        false
    );
    testAssertThrows(
        "RuntimeException",
        "mergeUserAccounts",
        [$pdo, $fixture["source_id"], $fixture["target"]["id"], $fixture["source_discord_id"]],
        "Validation : cible Discord-only refusée",
        "compte cible"
    );
    testAssertNoCopyFixtureUnchanged($pdo, $fixture);

    $fixture = testBuildNoCopyFixture(
        $pdo,
        null,
        testNewDiscordId($pdo),
        "",
        null,
        false
    );
    testAssertThrows(
        "RuntimeException",
        "mergeUserAccounts",
        [$pdo, $fixture["source_id"], $fixture["target"]["id"], $fixture["source_discord_id"]],
        "Validation : cible avec username vide refusée",
        "compte cible"
    );
    testAssertNoCopyFixtureUnchanged($pdo, $fixture);

    // Libère immédiatement l'index UNIQUE sur username = '' pour le test suivant.
    $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $deleteStmt->bindValue(":id", $fixture["target"]["id"], PDO::PARAM_INT);
    $deleteStmt->execute();

    $fixture = testBuildNoCopyFixture(
        $pdo,
        null,
        null,
        null,
        null,
        true
    );
    testAssertThrows(
        "RuntimeException",
        "mergeUserAccounts",
        [$pdo, $fixture["source_id"], $fixture["target"]["id"], testNewDiscordId($pdo)],
        "Validation : source sans Discord refusée",
        "pas lié à Discord"
    );
    testAssertNoCopyFixtureUnchanged($pdo, $fixture);

    $sourceDiscordId = testNewDiscordId($pdo);
    $otherDiscordId = testNewDiscordId($pdo);
    $fixture = testBuildNoCopyFixture(
        $pdo,
        null,
        $sourceDiscordId,
        null,
        null,
        true
    );
    testAssertThrows(
        "RuntimeException",
        "mergeUserAccounts",
        [$pdo, $fixture["source_id"], $fixture["target"]["id"], $otherDiscordId],
        "Validation : Discord source différent refusé",
        "ne correspond"
    );
    testAssertNoCopyFixtureUnchanged($pdo, $fixture);

    $fixture = testBuildNoCopyFixture(
        $pdo,
        null,
        testNewDiscordId($pdo),
        null,
        testNewDiscordId($pdo),
        true
    );
    testAssertThrows(
        "RuntimeException",
        "mergeUserAccounts",
        [$pdo, $fixture["source_id"], $fixture["target"]["id"], $fixture["source_discord_id"]],
        "Validation : cible déjà liée à un autre Discord refusée",
        "déjà lié"
    );
    testAssertNoCopyFixtureUnchanged($pdo, $fixture);

    $fixture = testBuildNoCopyFixture(
        $pdo,
        null,
        testNewDiscordId($pdo),
        null,
        null,
        true
    );
    $newSourceDiscordId = testNewDiscordId($pdo);
    testSetDiscordId($pdo, $fixture["source_id"], $newSourceDiscordId, "2020-01-03 00:00:00");
    $fixture["source_discord_id"] = $newSourceDiscordId;
    testAssertThrows(
        "RuntimeException",
        "mergeUserAccounts",
        [$pdo, $fixture["source_id"], $fixture["target"]["id"], testNewDiscordId($pdo)],
        "Validation : changement Discord source avant fusion refusé",
        "ne correspond"
    );
    testAssertNoCopyFixtureUnchanged($pdo, $fixture);

    $fixture = testBuildNoCopyFixture(
        $pdo,
        null,
        testNewDiscordId($pdo),
        null,
        null,
        true
    );
    testSetDiscordId(
        $pdo,
        $fixture["target"]["id"],
        testNewDiscordId($pdo),
        "2021-01-01 00:00:00"
    );
    testAssertThrows(
        "RuntimeException",
        "mergeUserAccounts",
        [$pdo, $fixture["source_id"], $fixture["target"]["id"], $fixture["source_discord_id"]],
        "Validation : liaison concurrente de la cible refusée",
        "déjà lié"
    );
    testAssertNotNull(
        testGetUser($pdo, $fixture["source_id"]),
        "Validation : source intacte après liaison concurrente"
    );
    testAssertSame(
        1,
        testCountRows($pdo, "user_pictures", "user_id = ?", [$fixture["source_id"]]),
        "Validation : photo source intacte après liaison concurrente"
    );
    testAssertSame(
        0,
        testCountRows($pdo, "user_pictures", "user_id = ?", [$fixture["target"]["id"]]),
        "Validation : aucune photo copiée après liaison concurrente"
    );

    $fixture = testBuildNoCopyFixture(
        $pdo,
        null,
        testNewDiscordId($pdo),
        null,
        null,
        true
    );
    $sourceId = $fixture["source_id"];
    $targetId = $fixture["target"]["id"];
    $sourceDiscordId = $fixture["source_discord_id"];
    $sourceFileAbsolute = (string)$_SERVER["DOCUMENT_ROOT"] . "/" . $fixture["source_file"];
    $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $deleteStmt->bindValue(":id", $sourceId, PDO::PARAM_INT);
    $deleteStmt->execute();

    testAssertThrows(
        "RuntimeException",
        "mergeUserAccounts",
        [$pdo, $sourceId, $targetId, $sourceDiscordId],
        "Validation : source supprimée avant fusion refusée",
        "introuvable"
    );
    testAssertSame(null, testGetUser($pdo, $sourceId), "Validation : source absente confirmée");
    testAssertNotNull(testGetUser($pdo, $targetId), "Validation : cible intacte après source absente");
    testAssertSame(
        0,
        testCountRows($pdo, "user_pictures", "user_id = ?", [$targetId]),
        "Validation : aucune photo copiée après source absente"
    );
    testAssertTrue(is_file($sourceFileAbsolute), "Validation : fichier source orphelin conservé");

    $fixture = testBuildNoCopyFixture(
        $pdo,
        null,
        testNewDiscordId($pdo),
        null,
        null,
        true
    );
    $sourceId = $fixture["source_id"];
    $targetId = $fixture["target"]["id"];
    $sourceDiscordId = $fixture["source_discord_id"];
    $deleteStmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $deleteStmt->bindValue(":id", $targetId, PDO::PARAM_INT);
    $deleteStmt->execute();

    testAssertThrows(
        "RuntimeException",
        "mergeUserAccounts",
        [$pdo, $sourceId, $targetId, $sourceDiscordId],
        "Validation : cible supprimée avant fusion refusée",
        "introuvable"
    );
    testAssertNotNull(testGetUser($pdo, $sourceId), "Validation : source intacte après cible absente");
    testAssertSame(
        1,
        testCountRows($pdo, "user_pictures", "user_id = ?", [$sourceId]),
        "Validation : photo source conservée après cible absente"
    );

    $target = testCreateClassicUser($pdo);
    testAssertThrows(
        "RuntimeException",
        "mergeUserAccounts",
        [$pdo, $target["id"], $target["id"], testNewDiscordId($pdo)],
        "Validation : fusion avec soi-même refusée",
        "lui-même"
    );
    testAssertNotNull(testGetUser($pdo, $target["id"]), "Validation : compte conservé après fusion avec soi-même");

    $invalidCases = [
        [0, $target["id"]],
        [-1, $target["id"]],
        [$target["id"], 0],
        [$target["id"], -1],
        [0, -1],
    ];

    foreach ($invalidCases as $invalidCase) {
        testAssertThrows(
            "RuntimeException",
            "mergeUserAccounts",
            [$pdo, $invalidCase[0], $invalidCase[1], testNewDiscordId($pdo)],
            "Validation : identifiants invalides refusés",
            "invalide"
        );
    }
    testAssertNotNull(testGetUser($pdo, $target["id"]), "Validation : compte conservé après identifiants invalides");
}

function testBuildFullMergeFixture(PDO $pdo): array
{
    $source = testCreateDiscordOnlyUser(
        $pdo,
        "2020-02-02 10:00:00",
        "2020-02-03 10:00:00"
    );
    $target = testCreateClassicUser($pdo, "2022-03-03 10:00:00", null);

    $sourceFiles = [
        "source.jpg" => "source-main",
        "collision.jpg" => "source-collision",
        "orphan.txt" => "source-orphan",
        "noext" => "source-noext",
        "éà photo.jpg" => "source-unicode",
        ".hidden" => "source-hidden",
    ];

    $sourcePaths = [];
    foreach ($sourceFiles as $filename => $content) {
        $sourcePaths[$filename] = testCreateProfileFile($source["id"], $filename, $content);
    }

    $targetFiles = [
        "existing.jpg" => "target-existing",
        "collision.jpg" => "target-collision",
        "collision_1.jpg" => "target-collision-1",
    ];

    $targetPaths = [];
    foreach ($targetFiles as $filename => $content) {
        $targetPaths[$filename] = testCreateProfileFile($target["id"], $filename, $content);
    }

    $sourcePictureFiles = ["source.jpg", "collision.jpg", "éà photo.jpg"];
    foreach ($sourcePictureFiles as $filename) {
        testInsertPicture($pdo, $source["id"], $filename, $sourcePaths[$filename]);
    }

    foreach ($targetFiles as $filename => $content) {
        testInsertPicture($pdo, $target["id"], $filename, $targetPaths[$filename]);
    }

    testSetTotalViews($pdo, "user_total_views", $source["id"], 5);
    testSetTotalViews($pdo, "user_total_views", $target["id"], 2);
    testSetPageViews($pdo, "user_page_views", $source["id"], "/full/a", 3);
    testSetPageViews($pdo, "user_page_views", $source["id"], "/full/b", 1);
    testSetPageViews($pdo, "user_page_views", $target["id"], "/full/a", 4);
    testSetPageViews($pdo, "user_page_views", $target["id"], "/full/c", 2);

    testSetTotalViews($pdo, "user_api_total_views", $source["id"], 7);
    testSetTotalViews($pdo, "user_api_total_views", $target["id"], 1);
    testSetPageViews($pdo, "user_api_page_views", $source["id"], "/api/full/a", 2);
    testSetPageViews($pdo, "user_api_page_views", $source["id"], "/api/full/b", 3);
    testSetPageViews($pdo, "user_api_page_views", $target["id"], "/api/full/a", 5);
    testSetPageViews($pdo, "user_api_page_views", $target["id"], "/api/full/c", 4);

    $rateEndpoint = "test_merge_rate_full";
    testInsertRateLimit($pdo, "user_" . $source["id"], $rateEndpoint, "2020-02-04 10:00:00");
    testInsertRateLimit($pdo, "user_" . $source["id"], $rateEndpoint, "2020-02-05 10:00:00");
    testInsertRateLimit($pdo, "user_" . $target["id"], $rateEndpoint, "2022-03-04 10:00:00");

    return [
        "source" => $source,
        "target" => $target,
        "source_files" => $sourceFiles,
        "source_paths" => $sourcePaths,
        "target_files" => $targetFiles,
        "target_paths" => $targetPaths,
        "rate_endpoint" => $rateEndpoint,
    ];
}

function testScenarioFullMerge(): void
{
    global $pdo;

    $fixture = testBuildFullMergeFixture($pdo);
    $source = $fixture["source"];
    $target = $fixture["target"];

    mergeUserAccounts($pdo, $source["id"], $target["id"], $source["discord_id"]);

    $targetRow = testGetUser($pdo, $target["id"]);
    testAssertNotNull($targetRow, "Fusion complète : compte cible présent");
    testAssertSame($target["id"], (int)$targetRow["id"], "Fusion complète : id cible conservé");
    testAssertSame($target["username"], $targetRow["username"], "Fusion complète : username cible conservé");
    testAssertSame($target["password"], $targetRow["password"], "Fusion complète : mot de passe cible conservé");
    testAssertSame(
        $source["discord_id"],
        (string)$targetRow["discord_id"],
        "Fusion complète : Discord source transféré"
    );
    testAssertSame(
        "2020-02-02 10:00:00",
        $targetRow["created_at"],
        "Fusion complète : created_at le plus ancien conservé"
    );
    testAssertSame(
        "2020-02-03 10:00:00",
        $targetRow["discord_linked_at"],
        "Fusion complète : discord_linked_at le plus ancien conservé"
    );
    testAssertSame(null, testGetUser($pdo, $source["id"]), "Fusion complète : compte source supprimé");

    testAssertSame(
        6,
        testCountRows($pdo, "user_pictures", "user_id = ?", [$target["id"]]),
        "Fusion complète : toutes les photos sont sur la cible"
    );
    testAssertSame(
        0,
        testCountRows($pdo, "user_pictures", "user_id = ?", [$source["id"]]),
        "Fusion complète : plus de photo sur la source"
    );

    $targetDirectory = testProfileDirectory($target["id"]);
    $sourceDirectory = testProfileDirectory($source["id"]);

    testAssertTrue(!is_dir($sourceDirectory), "Fusion complète : dossier source supprimé");
    testAssertSame(
        "target-existing",
        file_get_contents($targetDirectory . "/existing.jpg"),
        "Fusion complète : photo cible existante intacte"
    );
    testAssertSame(
        "target-collision",
        file_get_contents($targetDirectory . "/collision.jpg"),
        "Fusion complète : première collision cible intacte"
    );
    testAssertSame(
        "target-collision-1",
        file_get_contents($targetDirectory . "/collision_1.jpg"),
        "Fusion complète : deuxième collision cible intacte"
    );
    testAssertSame(
        "source-main",
        file_get_contents($targetDirectory . "/source.jpg"),
        "Fusion complète : photo source copiée"
    );
    testAssertSame(
        "source-collision",
        file_get_contents($targetDirectory . "/collision_2.jpg"),
        "Fusion complète : collision renommée en _2"
    );
    testAssertSame(
        "source-unicode",
        file_get_contents($targetDirectory . "/éà photo.jpg"),
        "Fusion complète : nom Unicode conservé"
    );
    testAssertSame(
        "source-orphan",
        file_get_contents($targetDirectory . "/orphan.txt"),
        "Fusion complète : fichier orphelin copié"
    );
    testAssertSame(
        "source-noext",
        file_get_contents($targetDirectory . "/noext"),
        "Fusion complète : fichier sans extension copié"
    );
    testAssertSame(
        "source-hidden",
        file_get_contents($targetDirectory . "/.hidden"),
        "Fusion complète : fichier caché copié"
    );

    $sourcePicture = testGetPictureByFilename($pdo, $target["id"], "source.jpg");
    testAssertNotNull($sourcePicture, "Fusion complète : ligne source.jpg présente");
    testAssertSame(
        "uploads/profile_pictures/" . $target["id"] . "/source.jpg",
        $sourcePicture["file_path"],
        "Fusion complète : chemin source.jpg réaffecté"
    );

    $collisionPicture = testGetPictureByFilename($pdo, $target["id"], "collision_2.jpg");
    testAssertNotNull($collisionPicture, "Fusion complète : ligne collision_2.jpg présente");
    testAssertSame(
        "uploads/profile_pictures/" . $target["id"] . "/collision_2.jpg",
        $collisionPicture["file_path"],
        "Fusion complète : chemin collision_2.jpg réaffecté"
    );

    $existingPicture = testGetPictureByFilename($pdo, $target["id"], "existing.jpg");
    testAssertNotNull($existingPicture, "Fusion complète : ligne cible existante présente");
    testAssertSame(
        $fixture["target_paths"]["existing.jpg"],
        $existingPicture["file_path"],
        "Fusion complète : chemin cible existant inchangé"
    );

    testAssertSame(
        7,
        testGetTotalViews($pdo, "user_total_views", $target["id"]),
        "Fusion complète : vues totales cumulées"
    );
    testAssertSame(
        8,
        testGetTotalViews($pdo, "user_api_total_views", $target["id"]),
        "Fusion complète : vues API totales cumulées"
    );

    $pageViews = testGetPageViews($pdo, "user_page_views", $target["id"]);
    $expectedPageViews = [
        "/full/a" => 7,
        "/full/b" => 1,
        "/full/c" => 2,
    ];
    testAssertSame($expectedPageViews, $pageViews, "Fusion complète : vues par page cumulées");

    $apiPageViews = testGetPageViews($pdo, "user_api_page_views", $target["id"]);
    $expectedApiPageViews = [
        "/api/full/a" => 7,
        "/api/full/b" => 3,
        "/api/full/c" => 4,
    ];
    testAssertSame($expectedApiPageViews, $apiPageViews, "Fusion complète : vues API par page cumulées");

    testAssertSame(
        0,
        testGetRateLimitCount($pdo, "user_" . $source["id"], $fixture["rate_endpoint"]),
        "Fusion complète : rate_limits source migrés"
    );
    testAssertSame(
        3,
        testGetRateLimitCount($pdo, "user_" . $target["id"], $fixture["rate_endpoint"]),
        "Fusion complète : rate_limits cible cumulés"
    );

    testAssertSame(
        0,
        testCountRows($pdo, "user_page_views", "user_id = ?", [$source["id"]]),
        "Fusion complète : plus de vues par page source"
    );
    testAssertSame(
        0,
        testCountRows($pdo, "user_total_views", "user_id = ?", [$source["id"]]),
        "Fusion complète : plus de vues totales source"
    );
    testAssertSame(
        0,
        testCountRows($pdo, "user_api_page_views", "user_id = ?", [$source["id"]]),
        "Fusion complète : plus de vues API par page source"
    );
    testAssertSame(
        0,
        testCountRows($pdo, "user_api_total_views", "user_id = ?", [$source["id"]]),
        "Fusion complète : plus de vues API totales source"
    );
}

function testScenarioReservedFilename(): void
{
    global $pdo;

    $source = testCreateDiscordOnlyUser($pdo, "2020-01-01 00:00:00", "2020-01-02 00:00:00");
    $target = testCreateClassicUser($pdo, "2022-01-01 00:00:00", null);

    $sourcePath = testCreateProfileFile($source["id"], "reserved.jpg", "source-reserved");
    testInsertPicture($pdo, $source["id"], "reserved.jpg", $sourcePath);

    $targetPath = "uploads/profile_pictures/" . $target["id"] . "/reserved.jpg";
    testInsertPicture($pdo, $target["id"], "reserved.jpg", $targetPath);

    mergeUserAccounts($pdo, $source["id"], $target["id"], $source["discord_id"]);

    testAssertSame(
        2,
        testCountRows($pdo, "user_pictures", "user_id = ?", [$target["id"]]),
        "Nom réservé : deux lignes sur la cible"
    );
    testAssertTrue(
        !file_exists(testProfileDirectory($target["id"]) . "/reserved.jpg"),
        "Nom réservé : fichier cible absent conservé absent"
    );

    $copiedPicture = testGetPictureByFilename($pdo, $target["id"], "reserved_1.jpg");
    testAssertNotNull($copiedPicture, "Nom réservé : fichier source renommé en _1");
    testAssertSame(
        "uploads/profile_pictures/" . $target["id"] . "/reserved_1.jpg",
        $copiedPicture["file_path"],
        "Nom réservé : chemin renommé en _1"
    );
    testAssertSame(
        "source-reserved",
        file_get_contents(testProfileDirectory($target["id"]) . "/reserved_1.jpg"),
        "Nom réservé : contenu source conservé"
    );

    $reservedPicture = testGetPictureByFilename($pdo, $target["id"], "reserved.jpg");
    testAssertNotNull($reservedPicture, "Nom réservé : ligne cible existante conservée");
    testAssertSame($targetPath, $reservedPicture["file_path"], "Nom réservé : chemin cible inchangé");
}

function testScenarioSourceEmptyData(): void
{
    global $pdo;

    $source = testCreateDiscordOnlyUser($pdo, "2020-01-01 00:00:00", "2020-01-02 00:00:00");
    $target = testCreateClassicUser($pdo, "2022-01-01 00:00:00", null);

    $targetPath = testCreateProfileFile($target["id"], "target_only.jpg", "target-only");
    testInsertPicture($pdo, $target["id"], "target_only.jpg", $targetPath);
    testSetTotalViews($pdo, "user_total_views", $target["id"], 9);
    testSetPageViews($pdo, "user_page_views", $target["id"], "/keep", 4);
    testSetTotalViews($pdo, "user_api_total_views", $target["id"], 6);
    testSetPageViews($pdo, "user_api_page_views", $target["id"], "/keep-api", 3);

    $rateEndpoint = "test_merge_rate_target_only";
    testInsertRateLimit($pdo, "user_" . $target["id"], $rateEndpoint, "2022-01-03 00:00:00");

    mergeUserAccounts($pdo, $source["id"], $target["id"], $source["discord_id"]);

    testAssertSame(null, testGetUser($pdo, $source["id"]), "Source vide : compte source supprimé");
    testAssertSame(
        1,
        testCountRows($pdo, "user_pictures", "user_id = ?", [$target["id"]]),
        "Source vide : photo cible unique"
    );
    testAssertSame(
        "target-only",
        file_get_contents(testProfileDirectory($target["id"]) . "/target_only.jpg"),
        "Source vide : photo cible intacte"
    );
    testAssertSame(
        9,
        testGetTotalViews($pdo, "user_total_views", $target["id"]),
        "Source vide : vues totales cible inchangées"
    );
    testAssertSame(
        6,
        testGetTotalViews($pdo, "user_api_total_views", $target["id"]),
        "Source vide : vues API totales cible inchangées"
    );
    testAssertSame(
        ["/keep" => 4],
        testGetPageViews($pdo, "user_page_views", $target["id"]),
        "Source vide : vues par page cible inchangées"
    );
    testAssertSame(
        ["/keep-api" => 3],
        testGetPageViews($pdo, "user_api_page_views", $target["id"]),
        "Source vide : vues API par page cible inchangées"
    );
    testAssertSame(
        1,
        testGetRateLimitCount($pdo, "user_" . $target["id"], $rateEndpoint),
        "Source vide : rate_limits cible inchangés"
    );
}

function testScenarioTargetEmptyData(): void
{
    global $pdo;

    $source = testCreateDiscordOnlyUser($pdo, "2020-01-01 00:00:00", "2020-01-02 00:00:00");
    $target = testCreateClassicUser($pdo, "2022-01-01 00:00:00", null);

    $sourcePath = testCreateProfileFile($source["id"], "only_source.jpg", "only-source");
    testInsertPicture($pdo, $source["id"], "only_source.jpg", $sourcePath);
    testSetTotalViews($pdo, "user_total_views", $source["id"], 3);
    testSetPageViews($pdo, "user_page_views", $source["id"], "/only-source", 2);
    testSetTotalViews($pdo, "user_api_total_views", $source["id"], 4);
    testSetPageViews($pdo, "user_api_page_views", $source["id"], "/only-api-source", 1);

    $rateEndpoint = "test_merge_rate_source_only";
    testInsertRateLimit($pdo, "user_" . $source["id"], $rateEndpoint, "2020-01-03 00:00:00");

    mergeUserAccounts($pdo, $source["id"], $target["id"], $source["discord_id"]);

    testAssertSame(null, testGetUser($pdo, $source["id"]), "Cible vide : compte source supprimé");
    testAssertSame(
        1,
        testCountRows($pdo, "user_pictures", "user_id = ?", [$target["id"]]),
        "Cible vide : photo source transférée"
    );
    testAssertSame(
        "only-source",
        file_get_contents(testProfileDirectory($target["id"]) . "/only_source.jpg"),
        "Cible vide : contenu photo transféré"
    );
    testAssertSame(
        "uploads/profile_pictures/" . $target["id"] . "/only_source.jpg",
        testGetPictureByFilename($pdo, $target["id"], "only_source.jpg")["file_path"],
        "Cible vide : chemin photo réaffecté"
    );
    testAssertSame(
        3,
        testGetTotalViews($pdo, "user_total_views", $target["id"]),
        "Cible vide : vues totales insérées"
    );
    testAssertSame(
        4,
        testGetTotalViews($pdo, "user_api_total_views", $target["id"]),
        "Cible vide : vues API totales insérées"
    );
    testAssertSame(
        ["/only-source" => 2],
        testGetPageViews($pdo, "user_page_views", $target["id"]),
        "Cible vide : vues par page insérées"
    );
    testAssertSame(
        ["/only-api-source" => 1],
        testGetPageViews($pdo, "user_api_page_views", $target["id"]),
        "Cible vide : vues API par page insérées"
    );
    testAssertSame(
        1,
        testGetRateLimitCount($pdo, "user_" . $target["id"], $rateEndpoint),
        "Cible vide : rate_limits transférés"
    );
    testAssertSame(
        0,
        testGetRateLimitCount($pdo, "user_" . $source["id"], $rateEndpoint),
        "Cible vide : rate_limits source vidés"
    );
}

function testScenarioSourceEmptyUsername(): void
{
    global $pdo;

    $source = testCreateDiscordOnlyUser(
        $pdo,
        "2020-01-01 00:00:00",
        "2020-01-02 00:00:00",
        ""
    );
    $target = testCreateClassicUser($pdo, "2022-01-01 00:00:00", null);

    $sourcePath = testCreateProfileFile($source["id"], "empty_username.jpg", "empty-username");
    testInsertPicture($pdo, $source["id"], "empty_username.jpg", $sourcePath);

    mergeUserAccounts($pdo, $source["id"], $target["id"], $source["discord_id"]);

    $targetRow = testGetUser($pdo, $target["id"]);
    testAssertSame(null, testGetUser($pdo, $source["id"]), "Username vide : compte source supprimé");
    testAssertSame(
        $source["discord_id"],
        (string)$targetRow["discord_id"],
        "Username vide : Discord transféré"
    );
    testAssertSame(
        "empty-username",
        file_get_contents(testProfileDirectory($target["id"]) . "/empty_username.jpg"),
        "Username vide : photo transférée"
    );
}

function testScenarioSourceFilesWithoutRows(): void
{
    global $pdo;

    $source = testCreateDiscordOnlyUser($pdo, "2020-01-01 00:00:00", "2020-01-02 00:00:00");
    $target = testCreateClassicUser($pdo, "2022-01-01 00:00:00", null);

    testCreateProfileFile($source["id"], "loose.jpg", "loose-file");

    mergeUserAccounts($pdo, $source["id"], $target["id"], $source["discord_id"]);

    testAssertSame(null, testGetUser($pdo, $source["id"]), "Fichiers sans ligne : source supprimée");
    testAssertSame(
        0,
        testCountRows($pdo, "user_pictures", "user_id = ?", [$target["id"]]),
        "Fichiers sans ligne : aucune ligne inventée"
    );
    testAssertSame(
        "loose-file",
        file_get_contents(testProfileDirectory($target["id"]) . "/loose.jpg"),
        "Fichiers sans ligne : fichier copié"
    );
    testAssertTrue(
        !is_dir(testProfileDirectory($source["id"])),
        "Fichiers sans ligne : dossier source supprimé"
    );
}

function testScenarioTargetPathIsFile(): void
{
    global $pdo;

    $source = testCreateDiscordOnlyUser($pdo, "2020-01-01 00:00:00", "2020-01-02 00:00:00");
    $target = testCreateClassicUser($pdo, "2022-01-01 00:00:00", null);

    $sourcePath = testCreateProfileFile($source["id"], "copy_failure.jpg", "copy-failure");
    testInsertPicture($pdo, $source["id"], "copy_failure.jpg", $sourcePath);
    $targetPath = testCreateProfilePathAsFile($target["id"], "not-a-directory");

    testAssertThrows(
        "RuntimeException",
        "mergeUserAccounts",
        [$pdo, $source["id"], $target["id"], $source["discord_id"]],
        "Échec copie : dossier cible impossible",
        "dossier de photos cible"
    );

    testAssertNotNull(testGetUser($pdo, $source["id"]), "Échec copie : source conservée");
    testAssertNotNull(testGetUser($pdo, $target["id"]), "Échec copie : cible conservée");
    testAssertSame(
        1,
        testCountRows($pdo, "user_pictures", "user_id = ?", [$source["id"]]),
        "Échec copie : ligne photo source conservée"
    );
    testAssertSame(
        0,
        testCountRows($pdo, "user_pictures", "user_id = ?", [$target["id"]]),
        "Échec copie : aucune ligne photo cible"
    );
    testAssertTrue(
        is_file((string)$_SERVER["DOCUMENT_ROOT"] . "/" . $sourcePath),
        "Échec copie : fichier source conservé"
    );
    testAssertSame(
        "not-a-directory",
        file_get_contents($targetPath),
        "Échec copie : chemin cible inchangé"
    );
    testAssertTrue(!$pdo->inTransaction(), "Échec copie : transaction annulée");
}

function testScenarioSourcePathIsFile(): void
{
    global $pdo;

    $source = testCreateDiscordOnlyUser($pdo, "2020-01-01 00:00:00", "2020-01-02 00:00:00");
    $target = testCreateClassicUser($pdo, "2022-01-01 00:00:00", null);

    $sourcePath = testCreateProfilePathAsFile($source["id"], "source-not-a-directory");
    $sourcePicturePath = "uploads/profile_pictures/" . $source["id"] . "/source_missing.jpg";
    testInsertPicture($pdo, $source["id"], "source_missing.jpg", $sourcePicturePath);

    testAssertThrows(
        "RuntimeException",
        "mergeUserAccounts",
        [$pdo, $source["id"], $target["id"], $source["discord_id"]],
        "Échec copie : dossier source impossible",
        "dossier de photos source"
    );

    testAssertNotNull(testGetUser($pdo, $source["id"]), "Échec source : source conservée");
    testAssertNotNull(testGetUser($pdo, $target["id"]), "Échec source : cible conservée");
    testAssertSame(
        1,
        testCountRows($pdo, "user_pictures", "user_id = ?", [$source["id"]]),
        "Échec source : ligne photo source conservée"
    );
    testAssertSame(
        0,
        testCountRows($pdo, "user_pictures", "user_id = ?", [$target["id"]]),
        "Échec source : aucune ligne photo cible"
    );
    testAssertSame(
        "source-not-a-directory",
        file_get_contents($sourcePath),
        "Échec source : chemin source inchangé"
    );
    testAssertTrue(!$pdo->inTransaction(), "Échec source : transaction annulée");
}

function testBuildRollbackFixture(PDO $pdo): array
{
    $source = testCreateDiscordOnlyUser(
        $pdo,
        "2020-01-01 00:00:00",
        "2020-01-02 00:00:00"
    );
    $target = testCreateClassicUser($pdo, "2022-01-01 00:00:00", null);

    $sourcePath = testCreateProfileFile(
        $source["id"],
        "rollback_source.jpg",
        "source-rollback"
    );
    $targetPath = testCreateProfileFile(
        $target["id"],
        "rollback_target.jpg",
        "target-rollback"
    );

    testInsertPicture($pdo, $source["id"], "rollback_source.jpg", $sourcePath);
    testInsertPicture($pdo, $target["id"], "rollback_target.jpg", $targetPath);

    testSetTotalViews($pdo, "user_total_views", $source["id"], 5);
    testSetTotalViews($pdo, "user_total_views", $target["id"], 2);
    testSetPageViews($pdo, "user_page_views", $source["id"], "/rollback/source", 3);
    testSetPageViews($pdo, "user_page_views", $target["id"], "/rollback/target", 4);

    testSetTotalViews($pdo, "user_api_total_views", $source["id"], 7);
    testSetTotalViews($pdo, "user_api_total_views", $target["id"], 1);
    testSetPageViews($pdo, "user_api_page_views", $source["id"], "/rollback/api/source", 2);
    testSetPageViews($pdo, "user_api_page_views", $target["id"], "/rollback/api/target", 3);

    $rateEndpoint = "test_merge_rate_rollback";
    testInsertRateLimit($pdo, "user_" . $source["id"], $rateEndpoint, "2020-01-03 00:00:00");
    testInsertRateLimit($pdo, "user_" . $target["id"], $rateEndpoint, "2022-01-03 00:00:00");

    return [
        "source" => $source,
        "target" => $target,
        "source_path" => $sourcePath,
        "target_path" => $targetPath,
        "rate_endpoint" => $rateEndpoint,
    ];
}

function testVerifyRollbackFixture(PDO $pdo, array $fixture): void
{
    $sourceId = $fixture["source"]["id"];
    $targetId = $fixture["target"]["id"];

    $sourceRow = testGetUser($pdo, $sourceId);
    testAssertNotNull($sourceRow, "Rollback : compte source restauré");
    testAssertSame(
        $fixture["source"]["discord_id"],
        (string)$sourceRow["discord_id"],
        "Rollback : Discord source restauré"
    );
    testAssertSame("2020-01-01 00:00:00", $sourceRow["created_at"], "Rollback : created_at source restauré");
    testAssertSame(
        "2020-01-02 00:00:00",
        $sourceRow["discord_linked_at"],
        "Rollback : discord_linked_at source restauré"
    );

    $targetRow = testGetUser($pdo, $targetId);
    testAssertNotNull($targetRow, "Rollback : compte cible présent");
    testAssertSame($fixture["target"]["username"], $targetRow["username"], "Rollback : username cible inchangé");
    testAssertSame($fixture["target"]["password"], $targetRow["password"], "Rollback : mot de passe cible inchangé");
    testAssertSame(null, $targetRow["discord_id"], "Rollback : cible toujours non liée");
    testAssertSame("2022-01-01 00:00:00", $targetRow["created_at"], "Rollback : created_at cible inchangé");
    testAssertSame(null, $targetRow["discord_linked_at"], "Rollback : discord_linked_at cible inchangé");

    testAssertSame(
        1,
        testCountRows($pdo, "user_pictures", "user_id = ?", [$sourceId]),
        "Rollback : photo source restaurée"
    );
    testAssertSame(
        1,
        testCountRows($pdo, "user_pictures", "user_id = ?", [$targetId]),
        "Rollback : photo cible inchangée"
    );

    $sourcePicture = testGetPictureByFilename($pdo, $sourceId, "rollback_source.jpg");
    testAssertNotNull($sourcePicture, "Rollback : ligne photo source restaurée");
    testAssertSame($fixture["source_path"], $sourcePicture["file_path"], "Rollback : chemin source restauré");

    $targetPicture = testGetPictureByFilename($pdo, $targetId, "rollback_target.jpg");
    testAssertNotNull($targetPicture, "Rollback : ligne photo cible présente");
    testAssertSame($fixture["target_path"], $targetPicture["file_path"], "Rollback : chemin cible inchangé");

    testAssertSame(
        5,
        testGetTotalViews($pdo, "user_total_views", $sourceId),
        "Rollback : vues totales source restaurées"
    );
    testAssertSame(
        2,
        testGetTotalViews($pdo, "user_total_views", $targetId),
        "Rollback : vues totales cible inchangées"
    );
    testAssertSame(
        7,
        testGetTotalViews($pdo, "user_api_total_views", $sourceId),
        "Rollback : vues API totales source restaurées"
    );
    testAssertSame(
        1,
        testGetTotalViews($pdo, "user_api_total_views", $targetId),
        "Rollback : vues API totales cible inchangées"
    );
    testAssertSame(
        ["/rollback/source" => 3],
        testGetPageViews($pdo, "user_page_views", $sourceId),
        "Rollback : vues par page source restaurées"
    );
    testAssertSame(
        ["/rollback/target" => 4],
        testGetPageViews($pdo, "user_page_views", $targetId),
        "Rollback : vues par page cible inchangées"
    );
    testAssertSame(
        ["/rollback/api/source" => 2],
        testGetPageViews($pdo, "user_api_page_views", $sourceId),
        "Rollback : vues API par page source restaurées"
    );
    testAssertSame(
        ["/rollback/api/target" => 3],
        testGetPageViews($pdo, "user_api_page_views", $targetId),
        "Rollback : vues API par page cible inchangées"
    );

    testAssertSame(
        1,
        testGetRateLimitCount($pdo, "user_" . $sourceId, $fixture["rate_endpoint"]),
        "Rollback : rate_limit source restauré"
    );
    testAssertSame(
        1,
        testGetRateLimitCount($pdo, "user_" . $targetId, $fixture["rate_endpoint"]),
        "Rollback : rate_limit cible inchangé"
    );

    $sourceFile = (string)$_SERVER["DOCUMENT_ROOT"] . "/" . $fixture["source_path"];
    $targetFile = (string)$_SERVER["DOCUMENT_ROOT"] . "/" . $fixture["target_path"];
    $copiedFile = testProfileDirectory($targetId) . "/rollback_source.jpg";

    testAssertSame("source-rollback", file_get_contents($sourceFile), "Rollback : fichier source intact");
    testAssertSame("target-rollback", file_get_contents($targetFile), "Rollback : fichier cible intact");
    testAssertTrue(!file_exists($copiedFile), "Rollback : copie cible supprimée");
}

function testBuildFailureTriggerSql(
    string $type,
    string $triggerName,
    array $fixture,
    string $unique
): string {
    $sourceId = $fixture["source"]["id"];
    $targetId = $fixture["target"]["id"];

    if ($type === "user_pictures_update") {
        return "CREATE TRIGGER `" . $triggerName . "`
            BEFORE UPDATE ON `user_pictures`
            FOR EACH ROW
            BEGIN
                IF OLD.user_id = " . $sourceId . " THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'test user_pictures update';
                END IF;
            END";
    }

    if ($type === "user_total_views_insert") {
        return "CREATE TRIGGER `" . $triggerName . "`
            BEFORE INSERT ON `user_total_views`
            FOR EACH ROW
            BEGIN
                IF NEW.user_id = " . $targetId . " THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'test user_total_views insert';
                END IF;
            END";
    }

    if ($type === "user_page_views_insert") {
        return "CREATE TRIGGER `" . $triggerName . "`
            BEFORE INSERT ON `user_page_views`
            FOR EACH ROW
            BEGIN
                IF NEW.user_id = " . $targetId . " THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'test user_page_views insert';
                END IF;
            END";
    }

    if ($type === "user_api_total_views_insert") {
        return "CREATE TRIGGER `" . $triggerName . "`
            BEFORE INSERT ON `user_api_total_views`
            FOR EACH ROW
            BEGIN
                IF NEW.user_id = " . $targetId . " THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'test user_api_total_views insert';
                END IF;
            END";
    }

    if ($type === "user_api_page_views_insert") {
        return "CREATE TRIGGER `" . $triggerName . "`
            BEFORE INSERT ON `user_api_page_views`
            FOR EACH ROW
            BEGIN
                IF NEW.user_id = " . $targetId . " THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'test user_api_page_views insert';
                END IF;
            END";
    }

    if ($type === "rate_limits_update") {
        return "CREATE TRIGGER `" . $triggerName . "`
            BEFORE UPDATE ON `rate_limits`
            FOR EACH ROW
            BEGIN
                IF OLD.identifier = 'user_" . $sourceId . "' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'test rate_limits update';
                END IF;
            END";
    }

    if ($type === "users_update") {
        return "CREATE TRIGGER `" . $triggerName . "`
            BEFORE UPDATE ON `users`
            FOR EACH ROW
            BEGIN
                IF OLD.id = " . $targetId . " THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'test users update';
                END IF;
            END";
    }

    if ($type === "users_delete") {
        return "CREATE TRIGGER `" . $triggerName . "`
            BEFORE DELETE ON `users`
            FOR EACH ROW
            BEGIN
                IF OLD.id = " . $sourceId . " THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'test users delete';
                END IF;
            END";
    }

    if ($type === "assertion") {
        return "CREATE TRIGGER `" . $triggerName . "`
            BEFORE UPDATE ON `users`
            FOR EACH ROW
            BEGIN
                IF OLD.id = " . $targetId . " THEN
                    INSERT INTO user_page_views (user_id, url, view_count)
                    VALUES (" . $sourceId . ", '/rollback/assert-trigger-" . $unique . "', 1);
                END IF;
            END";
    }

    if ($type === "rate_limits_assert") {
        return "CREATE TRIGGER `" . $triggerName . "`
            BEFORE UPDATE ON `users`
            FOR EACH ROW
            BEGIN
                IF OLD.id = " . $targetId . " THEN
                    INSERT INTO rate_limits (identifier, endpoint, requested_at)
                    VALUES ('user_" . $sourceId . "', 'test_merge_rate_assert_" . $unique . "', NOW());
                END IF;
            END";
    }

    throw new RuntimeException("Type de trigger de test inconnu : " . $type);
}

function testScenarioRollbackTriggers(): void
{
    global $pdo;

    $cases = [
        ["type" => "user_pictures_update", "class" => "PDOException", "message" => ""],
        ["type" => "user_total_views_insert", "class" => "PDOException", "message" => ""],
        ["type" => "user_page_views_insert", "class" => "PDOException", "message" => ""],
        ["type" => "user_api_total_views_insert", "class" => "PDOException", "message" => ""],
        ["type" => "user_api_page_views_insert", "class" => "PDOException", "message" => ""],
        ["type" => "rate_limits_update", "class" => "PDOException", "message" => ""],
        ["type" => "users_update", "class" => "PDOException", "message" => ""],
        ["type" => "users_delete", "class" => "PDOException", "message" => ""],
        ["type" => "assertion", "class" => "RuntimeException", "message" => "données du compte source"],
        ["type" => "rate_limits_assert", "class" => "RuntimeException", "message" => "rate limiting"],
    ];

    foreach ($cases as $case) {
        $fixture = testBuildRollbackFixture($pdo);
        $triggerName = "test_mrg_" . substr(testNewSuffix(), 0, 10);
        $unique = substr(testNewSuffix(), 0, 10);
        $triggerSql = testBuildFailureTriggerSql($case["type"], $triggerName, $fixture, $unique);

        if ($case["type"] === "rate_limits_assert") {
            testRegisterRateEndpoint("test_merge_rate_assert_" . $unique);
        }

        testCreateTrigger($pdo, $triggerName, $triggerSql);

        testAssertThrows(
            $case["class"],
            "mergeUserAccounts",
            [$pdo, $fixture["source"]["id"], $fixture["target"]["id"], $fixture["source"]["discord_id"]],
            "Rollback " . $case["type"],
            $case["message"]
        );

        testDropTrigger($pdo, $triggerName);
        testAssertTrue(!$pdo->inTransaction(), "Rollback " . $case["type"] . " : transaction fermée");
        testVerifyRollbackFixture($pdo, $fixture);
    }
}

function testScenarioLockTimeout(): void
{
    global $pdo;

    $fixture = testBuildNoCopyFixture(
        $pdo,
        null,
        testNewDiscordId($pdo),
        null,
        null,
        true
    );

    $dsn = "mysql:host=" . getenv("DB_IP") . ";dbname=" . getenv("DB_NAME");
    $lockPdo = new PDO($dsn, getenv("DB_USER"), getenv("DB_PASS"));
    $lockPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("SET SESSION innodb_lock_wait_timeout = 1");
    $lockPdo->beginTransaction();

    try {
        $sql = "SELECT id FROM users WHERE id = :id FOR UPDATE";
        $stmt = $lockPdo->prepare($sql);
        $stmt->bindValue(":id", $fixture["target"]["id"], PDO::PARAM_INT);
        $stmt->execute();

        $exception = testAssertThrows(
            "PDOException",
            "mergeUserAccounts",
            [$pdo, $fixture["source_id"], $fixture["target"]["id"], $fixture["source_discord_id"]],
            "Verrou : fusion bloquée par une transaction concurrente"
        );

        $errorNumber = 0;
        if ($exception instanceof PDOException) {
            if (isset($exception->errorInfo[1])) {
                $errorNumber = (int)$exception->errorInfo[1];
            }
        }
        testAssertSame(1205, $errorNumber, "Verrou : code d'erreur lock wait timeout");
        testAssertTrue(!$pdo->inTransaction(), "Verrou : transaction annulée");
    }
    finally {
        if ($lockPdo->inTransaction()) {
            $lockPdo->rollBack();
        }
        $pdo->exec("SET SESSION innodb_lock_wait_timeout = 50");
    }

    testAssertNoCopyFixtureUnchanged($pdo, $fixture);
}

function testScenarioUtilityAllowlists(): void
{
    global $pdo;

    testAssertThrows(
        "RuntimeException",
        "userMergeTotalViews",
        [$pdo, "users", 1, 2],
        "Utilitaires : table de vues totales refusée",
        "non autorisée"
    );
    testAssertThrows(
        "RuntimeException",
        "userMergePageViews",
        [$pdo, "users", 1, 2],
        "Utilitaires : table de vues par page refusée",
        "non autorisée"
    );
}

function testScenarioDirectHelpers(): void
{
    global $pdo;

    // 1) userMergeGetReservedFilenames retourne exactement les noms en base.
    $reservedUser = testCreateClassicUser($pdo);
    testInsertPicture(
        $pdo,
        $reservedUser["id"],
        "reserved_b.jpg",
        "uploads/profile_pictures/" . $reservedUser["id"] . "/reserved_b.jpg"
    );
    testInsertPicture(
        $pdo,
        $reservedUser["id"],
        "reserved_a.jpg",
        "uploads/profile_pictures/" . $reservedUser["id"] . "/reserved_a.jpg"
    );

    $reservedFilenames = userMergeGetReservedFilenames($pdo, $reservedUser["id"]);
    sort($reservedFilenames);
    testAssertSame(
        ["reserved_a.jpg", "reserved_b.jpg"],
        $reservedFilenames,
        "Direct : userMergeGetReservedFilenames retourne les noms attendus"
    );

    // 2) userMergeCopyProfilePictures retourne les mappings et copies réelles.
    $copySource = testCreateDiscordOnlyUser(
        $pdo,
        "2020-01-01 00:00:00",
        "2020-01-02 00:00:00"
    );
    $copyTarget = testCreateClassicUser($pdo);

    testCreateProfileFile($copySource["id"], "plain.jpg", "plain-content");
    testCreateProfileFile($copySource["id"], "reserved.jpg", "reserved-content");
    testCreateProfileFile($copySource["id"], "collision.jpg", "collision-content");
    testCreateProfileFile($copyTarget["id"], "collision.jpg", "target-collision");

    $copyResult = userMergeCopyProfilePictures(
        $copySource["id"],
        $copyTarget["id"],
        ["reserved.jpg"]
    );

    $sourcePrefix = "uploads/profile_pictures/" . $copySource["id"] . "/";
    $targetPrefix = "uploads/profile_pictures/" . $copyTarget["id"] . "/";

    testAssertSame(
        $targetPrefix . "plain.jpg",
        $copyResult["file_mappings"][$sourcePrefix . "plain.jpg"],
        "Direct : mapping plain.jpg"
    );
    testAssertSame(
        $targetPrefix . "reserved_1.jpg",
        $copyResult["file_mappings"][$sourcePrefix . "reserved.jpg"],
        "Direct : mapping reserved.jpg renommé"
    );
    testAssertSame(
        $targetPrefix . "collision_1.jpg",
        $copyResult["file_mappings"][$sourcePrefix . "collision.jpg"],
        "Direct : mapping collision.jpg renommé"
    );
    testAssertSame(
        3,
        count($copyResult["copied_source_files"]),
        "Direct : trois fichiers source copiés"
    );
    testAssertSame(
        3,
        count($copyResult["copied_target_files"]),
        "Direct : trois fichiers cible créés"
    );

    $expectedSourceFiles = [
        testProfileDirectory($copySource["id"]) . "/collision.jpg",
        testProfileDirectory($copySource["id"]) . "/plain.jpg",
        testProfileDirectory($copySource["id"]) . "/reserved.jpg",
    ];
    $actualSourceFiles = $copyResult["copied_source_files"];
    sort($expectedSourceFiles);
    sort($actualSourceFiles);
    testAssertSame(
        $expectedSourceFiles,
        $actualSourceFiles,
        "Direct : liste exacte des fichiers source copiés"
    );

    $expectedTargetFiles = [
        testProfileDirectory($copyTarget["id"]) . "/collision_1.jpg",
        testProfileDirectory($copyTarget["id"]) . "/plain.jpg",
        testProfileDirectory($copyTarget["id"]) . "/reserved_1.jpg",
    ];
    $actualTargetFiles = $copyResult["copied_target_files"];
    sort($expectedTargetFiles);
    sort($actualTargetFiles);
    testAssertSame(
        $expectedTargetFiles,
        $actualTargetFiles,
        "Direct : liste exacte des fichiers cible créés"
    );
    testAssertSame(
        "reserved-content",
        file_get_contents(testProfileDirectory($copyTarget["id"]) . "/reserved_1.jpg"),
        "Direct : contenu reserved.jpg copié"
    );
    testAssertSame(
        "collision-content",
        file_get_contents(testProfileDirectory($copyTarget["id"]) . "/collision_1.jpg"),
        "Direct : contenu collision.jpg copié"
    );
    testAssertSame(
        "target-collision",
        file_get_contents(testProfileDirectory($copyTarget["id"]) . "/collision.jpg"),
        "Direct : fichier cible existant intact"
    );

    userMergeCleanupSourcePictures($copySource["id"], $copyResult["copied_source_files"]);
    testAssertTrue(
        !is_dir(testProfileDirectory($copySource["id"])),
        "Direct : userMergeCleanupSourcePictures supprime le dossier source"
    );

    // 3) userMergeGetUsersForUpdate retourne les deux lignes demandées.
    $lockSource = testCreateDiscordOnlyUser(
        $pdo,
        "2020-01-01 00:00:00",
        "2020-01-02 00:00:00"
    );
    $lockTarget = testCreateClassicUser($pdo);
    $pdo->beginTransaction();

    try {
        $lockedUsers = userMergeGetUsersForUpdate(
            $pdo,
            $lockSource["id"],
            $lockTarget["id"]
        );

        testAssertTrue(
            array_key_exists("source", $lockedUsers),
            "Direct : userMergeGetUsersForUpdate retourne la clé source"
        );
        testAssertTrue(
            array_key_exists("target", $lockedUsers),
            "Direct : userMergeGetUsersForUpdate retourne la clé target"
        );
        testAssertSame(
            $lockSource["id"],
            (int)$lockedUsers["source"]["id"],
            "Direct : userMergeGetUsersForUpdate retourne la source"
        );
        testAssertSame(
            $lockTarget["id"],
            (int)$lockedUsers["target"]["id"],
            "Direct : userMergeGetUsersForUpdate retourne la cible"
        );
        testAssertSame(
            null,
            $lockedUsers["source"]["username"],
            "Direct : username source NULL"
        );
        testAssertSame(
            $lockTarget["username"],
            $lockedUsers["target"]["username"],
            "Direct : username cible attendu"
        );
    }
    finally {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }

    // 4) userMergeAssertNoSourceData ne lève rien si tout est migré, et lève
    // une exception dès qu'une donnée source subsiste.
    $assertSource = testCreateDiscordOnlyUser(
        $pdo,
        "2020-01-01 00:00:00",
        "2020-01-02 00:00:00"
    );

    userMergeAssertNoSourceData($pdo, $assertSource["id"]);
    testAssertTrue(true, "Direct : userMergeAssertNoSourceData passe sans donnée source");

    testInsertPicture(
        $pdo,
        $assertSource["id"],
        "assert_source.jpg",
        "uploads/profile_pictures/" . $assertSource["id"] . "/assert_source.jpg"
    );
    testAssertThrows(
        "RuntimeException",
        "userMergeAssertNoSourceData",
        [$pdo, $assertSource["id"]],
        "Direct : userMergeAssertNoSourceData détecte une photo source",
        "données du compte source"
    );

    // 5) Migrations directes des vues et des rate_limits.
    $migrationSource = testCreateDiscordOnlyUser(
        $pdo,
        "2020-01-01 00:00:00",
        "2020-01-02 00:00:00"
    );
    $migrationTarget = testCreateClassicUser($pdo);

    testSetTotalViews($pdo, "user_total_views", $migrationSource["id"], 5);
    testSetTotalViews($pdo, "user_total_views", $migrationTarget["id"], 2);
    testSetPageViews($pdo, "user_page_views", $migrationSource["id"], "/direct/a", 3);
    testSetPageViews($pdo, "user_page_views", $migrationSource["id"], "/direct/b", 1);
    testSetPageViews($pdo, "user_page_views", $migrationTarget["id"], "/direct/a", 4);

    testSetTotalViews($pdo, "user_api_total_views", $migrationSource["id"], 7);
    testSetTotalViews($pdo, "user_api_total_views", $migrationTarget["id"], 1);
    testSetPageViews($pdo, "user_api_page_views", $migrationSource["id"], "/direct/api/a", 2);

    testInsertRateLimit(
        $pdo,
        "user_" . $migrationSource["id"],
        "test_merge_direct_rate",
        "2020-01-03 00:00:00"
    );
    testInsertRateLimit(
        $pdo,
        "user_" . $migrationSource["id"],
        "test_merge_direct_rate",
        "2020-01-04 00:00:00"
    );
    testInsertRateLimit(
        $pdo,
        "user_" . $migrationTarget["id"],
        "test_merge_direct_rate",
        "2022-01-03 00:00:00"
    );

    testInsertPicture(
        $pdo,
        $migrationSource["id"],
        "direct_source.jpg",
        "uploads/profile_pictures/" . $migrationSource["id"] . "/direct_source.jpg"
    );
    testInsertPicture(
        $pdo,
        $migrationTarget["id"],
        "direct_target.jpg",
        "uploads/profile_pictures/" . $migrationTarget["id"] . "/direct_target.jpg"
    );

    userMergeTotalViews($pdo, "user_total_views", $migrationSource["id"], $migrationTarget["id"]);
    userMergeTotalViews($pdo, "user_api_total_views", $migrationSource["id"], $migrationTarget["id"]);
    userMergePageViews($pdo, "user_page_views", $migrationSource["id"], $migrationTarget["id"]);
    userMergePageViews($pdo, "user_api_page_views", $migrationSource["id"], $migrationTarget["id"]);
    userMergeRateLimits($pdo, $migrationSource["id"], $migrationTarget["id"]);
    userMergeProfilePictures(
        $pdo,
        $migrationSource["id"],
        $migrationTarget["id"],
        [
            "uploads/profile_pictures/" . $migrationSource["id"] . "/direct_source.jpg"
                => "uploads/profile_pictures/" . $migrationTarget["id"] . "/direct_source.jpg",
        ]
    );

    testAssertSame(
        7,
        testGetTotalViews($pdo, "user_total_views", $migrationTarget["id"]),
        "Direct : userMergeTotalViews cumule les vues"
    );
    testAssertSame(
        null,
        testGetTotalViews($pdo, "user_total_views", $migrationSource["id"]),
        "Direct : userMergeTotalViews supprime la source"
    );
    testAssertSame(
        8,
        testGetTotalViews($pdo, "user_api_total_views", $migrationTarget["id"]),
        "Direct : userMergeTotalViews cumule les vues API"
    );
    testAssertSame(
        null,
        testGetTotalViews($pdo, "user_api_total_views", $migrationSource["id"]),
        "Direct : userMergeTotalViews supprime la source API"
    );
    testAssertSame(
        ["/direct/a" => 7, "/direct/b" => 1],
        testGetPageViews($pdo, "user_page_views", $migrationTarget["id"]),
        "Direct : userMergePageViews cumule les URL"
    );
    testAssertSame(
        [],
        testGetPageViews($pdo, "user_page_views", $migrationSource["id"]),
        "Direct : userMergePageViews supprime la source"
    );
    testAssertSame(
        ["/direct/api/a" => 2],
        testGetPageViews($pdo, "user_api_page_views", $migrationTarget["id"]),
        "Direct : userMergePageViews insère les vues API"
    );
    testAssertSame(
        3,
        testGetRateLimitCount($pdo, "user_" . $migrationTarget["id"], "test_merge_direct_rate"),
        "Direct : userMergeRateLimits cumule les lignes"
    );
    testAssertSame(
        0,
        testGetRateLimitCount($pdo, "user_" . $migrationSource["id"], "test_merge_direct_rate"),
        "Direct : userMergeRateLimits vide la source"
    );

    $movedPicture = testGetPictureByFilename(
        $pdo,
        $migrationTarget["id"],
        "direct_source.jpg"
    );
    testAssertNotNull($movedPicture, "Direct : userMergeProfilePictures déplace la ligne");
    testAssertSame(
        "uploads/profile_pictures/" . $migrationTarget["id"] . "/direct_source.jpg",
        $movedPicture["file_path"],
        "Direct : userMergeProfilePictures met à jour le chemin"
    );
    testAssertSame(
        0,
        testCountRows($pdo, "user_pictures", "user_id = ?", [$migrationSource["id"]]),
        "Direct : userMergeProfilePictures vide la source"
    );

    userMergeAssertNoSourceData($pdo, $migrationSource["id"]);
    testAssertTrue(true, "Direct : toutes les données directes ont été migrées");

    // 6) Helpers Discord utilisés par le flux.
    $discordId = testNewDiscordId($pdo);
    $createdId = discordCreateUser($pdo, $discordId);
    testRegisterUser($createdId);

    $createdRow = testGetUser($pdo, $createdId);
    testAssertSame($createdId, (int)$createdRow["id"], "Direct : discordCreateUser retourne l'id");
    testAssertSame(null, $createdRow["username"], "Direct : discordCreateUser laisse username NULL");
    testAssertSame(null, $createdRow["password"], "Direct : discordCreateUser laisse password NULL");
    testAssertSame($discordId, (string)$createdRow["discord_id"], "Direct : discordCreateUser enregistre le Discord");
    testAssertNotNull($createdRow["discord_linked_at"], "Direct : discordCreateUser date la liaison");

    testAssertThrows(
        "PDOException",
        "discordCreateUser",
        [$pdo, $discordId],
        "Direct : discordCreateUser refuse un Discord déjà présent"
    );

    $linkUser = testCreateClassicUser($pdo);
    $linkDiscordId = testNewDiscordId($pdo);
    testAssertSame(
        1,
        discordLinkToUnlinkedUser($pdo, $linkUser["id"], $linkDiscordId),
        "Direct : discordLinkToUnlinkedUser lie un compte libre"
    );

    $linkedRow = testGetUser($pdo, $linkUser["id"]);
    testAssertSame($linkDiscordId, (string)$linkedRow["discord_id"], "Direct : Discord enregistré");
    testAssertNotNull($linkedRow["discord_linked_at"], "Direct : date de liaison enregistrée");
    testAssertSame(
        0,
        discordLinkToUnlinkedUser($pdo, $linkUser["id"], testNewDiscordId($pdo)),
        "Direct : discordLinkToUnlinkedUser ne remplace pas un Discord existant"
    );
    $linkedRowAfter = testGetUser($pdo, $linkUser["id"]);
    testAssertSame($linkDiscordId, (string)$linkedRowAfter["discord_id"], "Direct : Discord conservé");

    $deleteUser = testCreateDiscordOnlyUser(
        $pdo,
        "2020-01-01 00:00:00",
        "2020-01-02 00:00:00"
    );
    testInsertPicture(
        $pdo,
        $deleteUser["id"],
        "delete_cascade.jpg",
        "uploads/profile_pictures/" . $deleteUser["id"] . "/delete_cascade.jpg"
    );
    testSetTotalViews($pdo, "user_total_views", $deleteUser["id"], 4);

    discordDeleteUser($pdo, $deleteUser["id"]);
    testAssertSame(null, testGetUser($pdo, $deleteUser["id"]), "Direct : discordDeleteUser supprime la ligne");
    testAssertSame(
        0,
        testCountRows($pdo, "user_pictures", "user_id = ?", [$deleteUser["id"]]),
        "Direct : discordDeleteUser supprime les photos en cascade"
    );
    testAssertSame(
        0,
        testCountRows($pdo, "user_total_views", "user_id = ?", [$deleteUser["id"]]),
        "Direct : discordDeleteUser supprime les vues en cascade"
    );
}

$scenarios = [
    "Fonctions de dates" => "testScenarioDateHelpers",
    "Nom de fichier unique" => "testScenarioFilenameHelper",
    "Helpers Discord" => "testScenarioDiscordHelpers",
    "Validations et courses" => "testScenarioValidation",
    "Fusion complète" => "testScenarioFullMerge",
    "Nom de fichier réservé en base" => "testScenarioReservedFilename",
    "Source sans données" => "testScenarioSourceEmptyData",
    "Cible sans données" => "testScenarioTargetEmptyData",
    "Username source vide" => "testScenarioSourceEmptyUsername",
    "Fichiers source sans ligne" => "testScenarioSourceFilesWithoutRows",
    "Dossier cible impossible" => "testScenarioTargetPathIsFile",
    "Dossier source impossible" => "testScenarioSourcePathIsFile",
    "Rollbacks par trigger" => "testScenarioRollbackTriggers",
    "Verrou concurrent" => "testScenarioLockTimeout",
    "Listes blanches utilitaires" => "testScenarioUtilityAllowlists",
    "Retours directs des helpers" => "testScenarioDirectHelpers",
];

try {
    foreach ($scenarios as $label => $functionName) {
        $GLOBALS["testCurrentScenario"] = $label;
        echo "[TEST] " . $label . "\n";
        call_user_func($functionName);
    }

    echo "[OK] " . $GLOBALS["testAssertions"] . " assertions réussies.\n";
    exit(0);
}
catch (Throwable $e) {
    fwrite(
        STDERR,
        "[FAIL] " . $GLOBALS["testCurrentScenario"] . " : " . $e->getMessage() . "\n"
    );
    fwrite(STDERR, $e->getTraceAsString() . "\n");
    exit(1);
}

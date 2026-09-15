<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/loadEnv.php';
    $servername = getenv('DB_IP');
    $username = getenv('DB_USER');
    $password = getenv('DB_PASS');
    $dbname = getenv('DB_NAME');
    $forceInit = filter_var(getenv('FORCE_DB_INIT'), FILTER_VALIDATE_BOOLEAN) || filter_var(getenv('LOCAL'), FILTER_VALIDATE_BOOLEAN) || filter_var(getenv('FIRST_RUN'), FILTER_VALIDATE_BOOLEAN);
    $isAdmin = isset($_SESSION["is_admin"]) && !empty($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true;

    if (!$isAdmin && !$forceInit) {
        echo "<p>Accès refusé : droits administrateur requis</p>";
    }
    else {
        echo "<p>Connecting to database...</p>";
        echo "<p>DB_NAME: $dbname</p>";
        if (empty($dbname)) {
            echo "<p>DB_NAME environment variable is not set</p>";
            exit;
        }

        $sqlFile = $_SERVER['DOCUMENT_ROOT'] . '/src/sql/init-db.sql';
        if (!file_exists($sqlFile)) {
            echo "<p>SQL file not found: $sqlFile</p>";
            exit;
        }

        $sql = file_get_contents($sqlFile);

        try {
            $pdo = new PDO("mysql:host=$servername", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "<p>Could not connect. " . $e->getMessage() . "</p>";
            exit;
        }

        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $stmt) {
            if ($stmt === '') continue;
            try {
                $pdo->exec($stmt);
            } catch(PDOException $e) {
                echo "<p>Error executing: " . $e->getMessage() . "</p>";
            }
        }

        echo "<p>✔️ Database initialized from $sqlFile</p>";
    }
?>
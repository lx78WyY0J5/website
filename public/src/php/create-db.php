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
        echo "<p>DB_NAME: " . $dbname . "</p>";
        if (empty($dbname)) {
            die("DB_NAME environment variable is not set.<br>");
        }

        // Connect WITHOUT specifying the database first
        try {
            $pdo = new PDO("mysql:host=$servername", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Could not connect. " . $e->getMessage());
        }

        // Create database only if it doesn't exist
        try {
            $safeName = '`' . str_replace('`', '``', $dbname) . '`';
            $pdo->exec("CREATE DATABASE IF NOT EXISTS $safeName");
            $pdo->exec("USE $safeName");
            echo "✔️ Database ready<br>";
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage() . "<br>";
        }

        // Create table users only if it doesn't exist
        try {
            $sql = "CREATE TABLE IF NOT EXISTS users (
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )";


            $pdo->exec($sql);
            echo "✔️ Table users ready";
        } catch(PDOException $e) {
            echo "Error creating table: " . $e->getMessage();
        }

        // Create table rate_limits only if it doesn't exist
        try {
            $sql = "CREATE TABLE IF NOT EXISTS rate_limits (
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                identifier VARCHAR(255) NOT NULL,
                endpoint VARCHAR(50) NOT NULL,
                requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_identifier_endpoint_time (identifier, endpoint, requested_at)
            )";

            $pdo->exec($sql);
            echo "✔️ Rate limit table ready<br>";
        } catch(PDOException $e) {
            echo "Error creating rate limit table: " . $e->getMessage();
        }

        // Create table user_pictures only if it doesn't exist
        try {
            $sql = "CREATE TABLE IF NOT EXISTS user_pictures (
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                filename VARCHAR(255) NOT NULL,
                file_path VARCHAR(255) NOT NULL,
                uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user_id (user_id)
            )";

            $pdo->exec($sql);
            echo "✔️ User pictures table ready<br>";
        } catch(PDOException $e) {
            echo "Error creating user pictures table: " . $e->getMessage();
        }

        // Create table site_views only if it doesn't exist
        try {
            $sql = "CREATE TABLE IF NOT EXISTS site_views (
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                total_views BIGINT NOT NULL DEFAULT 0
            )";

            $pdo->exec($sql);
            echo "✔️ Site views table ready<br>";
        } catch(PDOException $e) {
            echo "Error creating site views table: " . $e->getMessage();
        }

        // Create table page_views only if it doesn't exist
        try {
            $sql = "CREATE TABLE IF NOT EXISTS page_views (
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                url VARCHAR(500) NOT NULL,
                view_count BIGINT NOT NULL DEFAULT 0,
                UNIQUE KEY uk_url (url),
                INDEX idx_url (url)
            )";

            $pdo->exec($sql);
            echo "✔️ Page views table ready<br>";
        } catch(PDOException $e) {
            echo "Error creating page views table: " . $e->getMessage();
        }

        // Create table user_page_views only if it doesn't exist
        try {
            $sql = "CREATE TABLE IF NOT EXISTS user_page_views (
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                url VARCHAR(500) NOT NULL,
                view_count BIGINT NOT NULL DEFAULT 0,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY uk_user_url (user_id, url),
                INDEX idx_user_id (user_id),
                INDEX idx_url (url)
            )";

            $pdo->exec($sql);
            echo "✔️ User page views table ready<br>";
        } catch(PDOException $e) {
            echo "Error creating user page views table: " . $e->getMessage();
        }


        // Create table user_total_views only if it doesn't exist
        try {
            $sql = "CREATE TABLE IF NOT EXISTS user_total_views (
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                total_views BIGINT NOT NULL DEFAULT 0,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY uk_user_id (user_id)
            )";

            $pdo->exec($sql);
            echo "✔️ User total views table ready<br>";
        } catch(PDOException $e) {
            echo "Error creating user total views table: " . $e->getMessage();
        }

        // Insert initial site views only if not exists
        try {
            $pdo->exec("INSERT IGNORE INTO site_views (id, total_views) VALUES (1, 0)");
            echo "✔️ Initial site views inserted<br>";
        } catch(PDOException $e) {
            echo "Error inserting site views: " . $e->getMessage();
        }

        // Insert admin user if not exists
        $adminUsername = getenv('ADMIN_USERNAME');
        $adminPassword = getenv('ADMIN_PASSWORD');
        if ($adminUsername && $adminPassword) {
            try {
                $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password) VALUES (?, ?)");
                $stmt->execute([$adminUsername, password_hash($adminPassword, PASSWORD_DEFAULT)]);
                echo "✔️ Admin user inserted<br>";
            } catch(PDOException $e) {
                echo "Error inserting admin user: " . $e->getMessage() . "<br>";
            }
        }

        echo "<p>✔️ Database initialized</p>";
    }

  $pdo = null;
?>
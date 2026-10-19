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
        echo "<p>🛜 Connecting to database...</p>";
        echo "<p>📊 DB_NAME: " . $dbname . "</p>";
        if (empty($dbname)) {
            die("DB_NAME environment variable is not set");
        }

        // Connect WITHOUT specifying the database first
        try {
            $pdo = new PDO("mysql:host=$servername", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Could not connect : " . $e->getMessage());
        }

        // Create database only if it doesn't exist
        try {
            $safeName = '`' . str_replace('`', '``', $dbname) . '`';
            $pdo->exec("CREATE DATABASE IF NOT EXISTS $safeName");
            $pdo->exec("USE $safeName");
            echo "<p>✔️ Database ready</p>";
        } catch(PDOException $e) {
            echo "<p>Error: " . $e->getMessage() . "</p>";
        }

        // Create table users only if it doesn't exist
        try {
            $sql = "CREATE TABLE IF NOT EXISTS users (
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) NULL UNIQUE,
                password VARCHAR(255) NULL,
                discord_id BIGINT UNSIGNED NULL UNIQUE,
                discord_linked_at DATETIME NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )";

            $pdo->exec($sql);
            echo "<p>✔️ Table users ready</p>";
        } catch(PDOException $e) {
            echo "<p>Error creating table: " . $e->getMessage() . "</p>";
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
            echo "<p>✔️ Rate limit table ready</p>";
        } catch(PDOException $e) {
            echo "<p>Error creating rate limit table: " . $e->getMessage() . "</p>";
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
            echo "<p>✔️ User pictures table ready</p>";
        } catch(PDOException $e) {
            echo "<p>Error creating user pictures table: " . $e->getMessage() . "</p>";
        }

        // Create table site_views only if it doesn't exist
        try {
            $sql = "CREATE TABLE IF NOT EXISTS site_views (
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                total_views BIGINT NOT NULL DEFAULT 0
            )";

            $pdo->exec($sql);
            // Insert initial row if not exists (ensures counter works after DB reset)
            $pdo->exec("INSERT IGNORE INTO site_views (id, total_views) VALUES (1, 0)");
            echo "<p>✔️ Site views table ready</p>";
        } catch(PDOException $e) {
            echo "<p>Error creating site views table: " . $e->getMessage() . "</p>";
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
            echo "<p>✔️ Page views table ready</p>";
        } catch(PDOException $e) {
            echo "<p>Error creating page views table: " . $e->getMessage() . "</p>";
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
            echo "<p>✔️ User page views table ready</p>";
        } catch(PDOException $e) {
            echo "<p>Error creating user page views table: " . $e->getMessage() . "</p>";
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
            echo "<p>✔️ User total views table ready</p>";
        } catch(PDOException $e) {
            echo "<p>Error creating user total views table: " . $e->getMessage() . "</p>";
        }

        // Create table api_views only if it doesn't exist
        try {
            $sql = "CREATE TABLE IF NOT EXISTS api_views (
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                total_views BIGINT NOT NULL DEFAULT 0
            )";

            $pdo->exec($sql);
            // Insert initial row if not exists (ensures counter works after DB reset)
            $pdo->exec("INSERT IGNORE INTO api_views (id, total_views) VALUES (1, 0)");
            echo "<p>✔️ API views table ready</p>";
        } catch(PDOException $e) {
            echo "<p>Error creating site views table: " . $e->getMessage() . "</p>";
        }

        // Create table api_page_views only if it doesn't exist
        try {
            $sql = "CREATE TABLE IF NOT EXISTS api_page_views (
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                url VARCHAR(500) NOT NULL,
                view_count BIGINT NOT NULL DEFAULT 0,
                UNIQUE KEY uk_url (url),
                INDEX idx_url (url)
            )";

            $pdo->exec($sql);
            echo "<p>✔️ Page views table ready</p>";
        } catch(PDOException $e) {
            echo "<p>Error creating page views table: " . $e->getMessage() . "</p>";
        }

        // Create table user_api_total_views only if it doesn't exist
        try {
            $sql = "CREATE TABLE IF NOT EXISTS user_api_total_views (
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                total_views BIGINT NOT NULL DEFAULT 0,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY uk_user_id (user_id)
            )";

            $pdo->exec($sql);
            echo "<p>✔️ User api views table ready</p>";
        } catch(PDOException $e) {
            echo "<p>Error creating api total views table: " . $e->getMessage() . "</p>";
        }

        // Create table user_api_page_views only if it doesn't exist
        try {
            $sql = "CREATE TABLE IF NOT EXISTS user_api_page_views (
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
            echo "<p>✔️ User page views table ready</p>";
        } catch(PDOException $e) {
            echo "<p>Error creating user page views table: " . $e->getMessage() . "</p>";
        }

        // Create table data_views only if it doesn't exist
        try {
            $sql = "CREATE TABLE IF NOT EXISTS data_views (
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                total_views BIGINT NOT NULL DEFAULT 0
            )";

            $pdo->exec($sql);
            // Insert initial row if not exists (ensures counter works after DB reset)
            $pdo->exec("INSERT IGNORE INTO data_views (id, total_views) VALUES (1, 0)");
            echo "<p>✔️ Data views table ready</p>";
        } catch(PDOException $e) {
            echo "<p>Error creating data views table: " . $e->getMessage() . "</p>";
        }

        // Insert admin user if not exists
        $adminUsername = getenv('ADMIN_USERNAME');
        $adminPassword = getenv('ADMIN_PASSWORD');
        if ($adminUsername && $adminPassword) {
            try {
                $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password) VALUES (?, ?)");
                $stmt->execute([$adminUsername, password_hash($adminPassword, PASSWORD_DEFAULT)]);
                echo "<p>✔️ Admin user inserted</p>";
            } catch(PDOException $e) {
                echo "<p>Error inserting admin user: " . $e->getMessage() . "</p>";
            }
        }

        // Create table visites only if it doesn't exist
        try {
            $sql = "CREATE TABLE IF NOT EXISTS visites (
                ip VARCHAR(45) NOT NULL,
                date_visite DATETIME(6) NOT NULL,
                user_agent TEXT NOT NULL,
                app_version TEXT,
                app_name VARCHAR(255),
                app_code_name VARCHAR(255),
                product VARCHAR(255),
                product_sub VARCHAR(255),
                vendor VARCHAR(255),
                oscpu VARCHAR(255),
                platform VARCHAR(100),
                language VARCHAR(20),
                languages VARCHAR(255),
                webdriver TINYINT(1) UNSIGNED DEFAULT 0,
                java_enabled TINYINT(1) UNSIGNED DEFAULT 0,
                is_mobile TINYINT(1) UNSIGNED DEFAULT 0,
                user_agent_brands TEXT,
                plugins TEXT,
                headless_score TINYINT UNSIGNED DEFAULT 0,
                device_memory DECIMAL(4,2),
                hardware_concurrency SMALLINT UNSIGNED,
                time_zone VARCHAR(100),
                timezone_offset SMALLINT,
                on_line TINYINT(1) UNSIGNED DEFAULT 0,
                connection_effective_type VARCHAR(50),
                connection_downlink DECIMAL(5,2),
                connection_save_data TINYINT(1) UNSIGNED DEFAULT 0,
                local_ip VARCHAR(45),
                screen_resolution VARCHAR(30),
                color_depth SMALLINT UNSIGNED,
                pixel_depth SMALLINT UNSIGNED,
                gpu TEXT,
                gpu_adapter TEXT,
                battery_level DECIMAL(3,2),
                battery_charging TINYINT(1) UNSIGNED DEFAULT 0,
                cookies_enabled TINYINT(1) UNSIGNED DEFAULT 0,
                do_not_track TINYINT(1) UNSIGNED DEFAULT 0,
                referrer TEXT,
                path TEXT,
                source VARCHAR(20) DEFAULT '',
                scroll_percentage DECIMAL(5,2) DEFAULT 0,
                time_spent INT UNSIGNED DEFAULT 0,
                page_load_time INT UNSIGNED DEFAULT 0,
                mouse_positions TEXT,
                error_message TEXT,
                fingerprint CHAR(64),
                raw_data TEXT,
                PRIMARY KEY (ip, date_visite),
                INDEX idx_fingerprint (fingerprint),
                INDEX idx_date_visite (date_visite)
            )";

            $pdo->exec($sql);
            echo "<p>✔️ Table visites ready</p>";
        } catch(PDOException $e) {
            echo "<p>Error creating visites table: " . $e->getMessage() . "</p>";
        }

        echo "<p>✔️ Database initialized</p>";
    }

  $pdo = null;
?>
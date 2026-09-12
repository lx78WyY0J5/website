<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/loadEnv.php';
  $servername = getenv('DB_IP');
  $username = getenv('DB_USER');
  $password = getenv('DB_PASS');
  $dbname = getenv('DB_NAME');

  if (isset($_SESSION["is_admin"]) && !empty($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true) {
      echo "<p>Connecting to database...</p>";
      echo "<p>DB_NAME: $dbname</p>";
      if (empty($dbname)) {
        echo "<p>DB_NAME environment variable is not set</p>";
      }

    // Connect WITHOUT specifying the database first
    try {
      $pdo = new PDO("mysql:host=$servername", $username, $password);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
      echo "<p>Could not connect. " . $e->getMessage() . "</p>";
    }

    // Create database only if it doesn't exist
    try {
      $safeName = '`' . str_replace('`', '``', $dbname) . '`';
      $pdo->exec("CREATE DATABASE IF NOT EXISTS $safeName");
      $pdo->exec("USE $safeName");
      echo "<p>✔️ Database ready</p>";
    } catch(PDOException $e) {
      echo "<p>☠️ Error: " . $e->getMessage() . "</p>";
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
      echo "<p>✔️ Table users ready</p>";
    } catch(PDOException $e) {
      echo "<p>☠️ Error creating table: " . $e->getMessage() . "</p>";
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
      echo "<p>✔️ rate_limits table ready</p>";
    } catch(PDOException $e) {
      echo "<p>☠️ Error creating rate limit table: " . $e->getMessage() . "</p>";
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
      echo "<p>✔️ user_pictures table ready</p>";
    } catch(PDOException $e) {
      echo "<p>☠️ Error creating user_pictures table: " . $e->getMessage() . "</p>";
    }
  }
?>
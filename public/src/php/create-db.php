<?php
  $servername = getenv('DB_IP');
  $username = getenv('DB_USER');
  $password = getenv('DB_PASS');
  $dbname = getenv('DB_NAME');

  if (!isset($_SESSION['is_admin'])) {
    if(empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
      http_response_code(403);
      die("Access denied.");
    }
    http_response_code(403);
    die("Access denied.");
  }

    echo "Connecting to database...<br>";
    echo "DB_NAME: $dbname<br>";
    if (empty($dbname)) {
      die("DB_NAME environment variable is not set.");
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
    echo "Database ready<br>";
  } catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
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
    echo "Table ready";
  } catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
  }

  // Create table rate_limits only if it doesn't exist
  try {
    $sql = "CREATE TABLE IF NOT EXISTS rate_limits (
      id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
      identifier VARCHAR(255) NOT NULL,
      endpoint VARCHAR(50) NOT NULL,
      attempts INT NOT NULL DEFAULT 1,
      window_start DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      UNIQUE KEY unique_identifier_endpoint (identifier, endpoint)
    )";

    $pdo->exec($sql);
    echo "Rate limit table ready<br>";
  } catch(PDOException $e) {
    echo "Error creating rate limit table: " . $e->getMessage();
  }

  $pdo = null;
?>
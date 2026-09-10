<?php
  $servername = "127.0.0.1";
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
    $sql = "CREATE TABLE users (
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

  $pdo = null;
?>
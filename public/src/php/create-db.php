<?php
  $servername = "127.0.0.1";
  $username = getenv('DB_USER');
  $password = getenv('DB_PASS');
  $dbname = getenv('DB_NAME');

  if (!isset($_SESSION['is_admin'])) {
    http_response_code(403);
    die("Access denied.");
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

  // Create table only if it doesn't exist
  try {
    $sql = "CREATE TABLE IF NOT EXISTS my_table (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL
    )";
    $pdo->exec($sql);
    echo "Table ready";
  } catch(PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
  }

  $pdo = null;
?>
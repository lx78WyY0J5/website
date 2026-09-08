<?php
  $servername = "127.0.0.1";
  $username = getenv('DB_USER');
  $password = getenv('DB_PASS');
  $dbname = getenv('DB_NAME');

  try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
  }
?>
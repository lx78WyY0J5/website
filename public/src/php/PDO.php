<?php
  $servername = "127.0.0.1";
  $username = getenv('DB_USER');
  $password = getenv('DB_PASS');
  $dbname = getenv('DB_NAME');

  try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // set the PDO error mode to exception
  } 
  catch(PDOException $e) {
    if (in_array($e->errorInfo[1], [2002, 2003])) {
      echo "<h1>La base de données est hors ligne</h1><p>Navré pour l'incident technique</p><p>Merci de revenir plus tard ...</p>";
    }
    else {
      echo "Connection failed: " . $e->getMessage();
    }
  }
?>
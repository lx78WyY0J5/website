<?php
$servername = "127.0.0.1";
$username = "webuser";
$password = "strongpassword";
$dbname = "mydb";

try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
  // set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  echo "Connected successfully to db : " . $dbname . '@' . $servername;
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}
?>
<?php
$servername = "127.0.0.1";
$username = "webuser";
$password = "strongpassword";

try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // set the PDO error mode to exception
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch(PDOException $e) {
        die("Could not connect. " . $e->getMessage());
        }

        try {
          $sql = "CREATE DATABASE myDB";
            $conn->exec($sql);
              echo "Database created successfully";
              } catch(PDOException $e) {
                // Handle errors during db creation
                  echo "Error creating database: " . $sql . "<br>" . $e->getMessage();
                  }

                  // Close connection
                  $conn = null;
?>
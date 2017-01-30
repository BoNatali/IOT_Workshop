<?php

# Edit this to your needs
$servername = 'localhost';
$username = 'root';
$password = '';
$db = 'internet_of_things_workshop';

try {
  $pdo = new PDO(
      'mysql:host=' . $servername . ';dbname=' . $db,
      $username,
      $password
    );
} catch(PDOException $e) {
  die($e->getMessage());
}

return $pdo;

?>

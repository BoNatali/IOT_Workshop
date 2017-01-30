<?php
require_once('config.php');
require_once('util.php');
require_once('database.php');

# Checks if device is valid
if(!isset($_GET['d'])) {
  redirect(ROOT);
} else {
  $stmt = $pdo->prepare("SELECT * FROM device WHERE id = ?");
  if ($stmt->execute([$_GET['d']])) {
    if($stmt->rowCount() == 0) {
      header('Location: ' . ROOT);
      die();
    }
  }
}
?>

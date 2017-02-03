<?php
/*
 * Check if device id is provided and exists, else redirect to homepage
*/

require_once('config.php');
require_once('util.php');
require_once('database.php');
require_once('query.php');

// Check if device is set
if(!isset($_GET['d'])) {
  redirect(ROOT);
} else {
  // Check if device exists, if not redirect to homepage location
  if ($stmt = DatabaseQuery::readDevice($_GET['d'])) {
    if($stmt->rowCount() == 0) {
      redirect(ROOT);
    }
  }
}
?>

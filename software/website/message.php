<?php
  require_once('check.php');

  if(!isset($_GET['td'])) {
      echo 'Target device id not found!';
      exit;
  }
  
  if(!$stmt = DatabaseQuery::readDeviceConfigurationWithMessage($_GET['td'], $_GET['d'])) {
    echo 'Something has gone wrong!';
    exit;
  } 
  if($stmt->rowCount() == 1) {
      $row = $stmt->fetch();
  }
?>
<!doctype html>
<title>IoT Workshop</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="main.css">
<link href="https://fonts.googleapis.com/css?family=Maven+Pro" rel="stylesheet">
<div class="middle-container">
  <div>
    <h1 class="text-center">Sinterklaas</h1>
    <h2 class="text-center">Message of <?php echo $_GET['td']; ?></h2>
    <div class="vertical-gap-30 text-container">
    <?php
      echo $row['message'];
    ?>
    </div>
    <form action="dashboard.php#filter">
      <input type="hidden" name="f" value="in">
      <input type="hidden" name="d" value="<?php echo $_GET['d']; ?>">
      <div class="text-center">
        <button type="submit" class="std-button">< Back to dashboard</button>
      </div>
    </form>
  </div>
</div>

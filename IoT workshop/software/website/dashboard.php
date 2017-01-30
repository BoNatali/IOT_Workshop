<?php
  /*
    The dashboard displays the incoming and outgoing connections of the device.
  */
  require_once('check.php');

  // Outcoming connections
  $outgoing_stmt = $pdo->prepare("SELECT * FROM device_configuration WHERE device_id = ? AND temp = 0");
  if(!$outgoing_stmt->execute([$_GET['d']])) {
    echo 'Something has gone wrong!';
    exit;
  }
  $outgoing_count = $outgoing_stmt->rowCount();

  // When reading incoming connections the device id of the logged in user is the target device id
  $incoming_stmt = $pdo->prepare("SELECT * FROM device_configuration WHERE target_device_id = ? AND temp = 0");
  if(!$incoming_stmt->execute([$_GET['d']])) {
    echo 'Something has gone wrong!';
    exit;
  }

  $incoming_count = $incoming_stmt->rowCount();
  $filter_type = 'out';
  $filter_column = 'device_id';
  $display_column = 'target_device_id';
  $stmt = $outgoing_stmt;
  if(isset($_GET['f']) && $_GET['f'] == 'in') {
    $filter_type = 'in';
    $filter_column = 'target_device_id';
    $display_column = 'device_id';
    $stmt = $incoming_stmt;
  }
?>
<!doctype html>
<title>Dashboard - IoT Workshop</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="main.css">
<link href="https://fonts.googleapis.com/css?family=Maven+Pro" rel="stylesheet">
<div class="middle-container">
  <div>
    <h1 class="text-center">Hi <?php echo $_GET['d']; ?>,</h1>
    <h2 class="text-center">how are you?</h2>
    <div class="vertical-gap-30 text-center">
      <form action="device_configuration.php" class="inl">
        <input type="hidden" name="d" value="<?php echo $_GET['d']; ?>">
        <button type="submit" class="circle-button"><span>+</span></button>
      </form>
    </div>
    <form id="filter" action="#filter" class="middle-container">
      <input type="hidden" name="d" value="<?php echo $_GET['d']; ?>">
        <button class="filter-button <?php echo ($outgoing_count + $incoming_count == 0 ? 'left-br' : 'left-tbr'); ?>" title="Outgoing" name="f" value="out" <?php echo isset($_GET['f']) ? ($_GET['f'] == 'out' ? 'disabled' : '') : 'disabled'; ?>>
          <img src="img/icon_outgoing.png" width="17" height="17" class="button-image"> (<?php echo $outgoing_count; ?>)
        </button>
        <button class="filter-button <?php echo ($outgoing_count + $incoming_count == 0 ? 'right-br' : 'right-tbr'); ?>" title="Incoming" name="f" value="in" <?php echo isset($_GET['f']) ? ($_GET['f'] == 'in' ? 'disabled' : '') : ''; ?>>
          <img src="img/icon_incoming.png" width="17" height="17" class="button-image"> (<?php echo $incoming_count; ?>)
        </button>
    </form>
    <table>
      <?php
      // Display default the outgoing connections, else the displayed connections are based on the filter (in or out)
            foreach($stmt->fetchAll() as $row) {
              echo '<tr>
                      <td>
                        <b>' . $row[$display_column] . '</b>
                      </td>
                      <td class="small-cell">';
                      if ($filter_type == 'in') {
                        echo '<form action="message.php">
                          <input type="hidden" name="d" value="' . $row['target_device_id'] . '">
                          <input type="hidden" name="td" value="' . $row['device_id'] . '">
                          <button class="envelope icon" style="background-color:' . $row['color'] . ';" title="Message"></button>
                        </form>';
                      } else {
                        echo '<form action="device_configuration.php">
                          <input type="hidden" name="d" value="' . $row['device_id'] . '">
                          <input type="hidden" name="td" value="' . $row['target_device_id'] . '">
                          <button class="edit icon" name="t" value="e" style="background-color:' . $row['color'] . ';" title="Edit"></button>
                        </form>';
                      }
                      echo '</td>
                      <td>
                        <div class="vertical-gap-5 small-cell">
                          <form action="api.php">';
                            if ($filter_type == 'in') {
                              echo '<input type="hidden" name="d" value="' . $row['target_device_id'] . '">
                                    <input type="hidden" name="td" value="' . $row['device_id'] . '">
                                    <input type="hidden" name="b" value="' . ($row['blacklist'] == 1 ? 0 : 1) . '">
                                    <input type="hidden" name="r" value="/dashboard.php?d=' . $row['target_device_id'] . '&f=' . $filter_type . '#filter">';
                              if($row['blacklist'] == 1) {
                                  echo '<button type="submit" name="t" value="bdc" class="add icon" title="Remove from blacklist"></button>';
                              } else {
                                  echo '<button type="submit" name="t" value="bdc" class="delete icon" title="Blacklist"></button>';
                              }
                            } else {
                              echo '
                                <input type="hidden" name="d" value="' . $row['device_id'] . '">
                                <input type="hidden" name="td" value="' . $row['target_device_id'] . '">
                                <input type="hidden" name="r" value="/dashboard.php?d=' . $row['device_id'] . '&f=' . $filter_type . '#filter">
                                <button type="submit" name="t" value="rdc" class="delete icon" title="Delete"></button>';
                            }

                        echo '</form>
                        </div>
                      </td>
                    </tr>';
            }
      ?>
  </table>
</div>

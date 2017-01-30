<?php
  require_once('config.php');
  require_once('util.php');
  require_once('database.php');

  $response = -1;

  // Device id and type is needed for every operation
  if(isset($_GET['d']) && isset($_GET['t'])) {
    // Go through all available types of operations
    switch($_GET['t']) {
      case 'sdc': // set device configuration
        // c should be hue
        if(isset($_GET['c']) && isset($_GET['td'])) {
          // When color value is higher than 3 we interpret it as hex
          if(isset($_GET['cv']) && $_GET['cv'] == 'hue') {
            $hexcolor = hsl2hex([$_GET['c']/360, 1, 0.5]);
          } else {
            $hexcolor = '#' . validate_hex($_GET['c']);
          }

          // Spring constant (optional)
          $spring = isset($_GET['sc']) ? round((255 / 100) * $_GET['sc']) : 128;
          // Damp constant (optional)
          $damp = isset($_GET['dc']) ? round((255 / 100) * $_GET['dc']) : 128;
          // Message (optional)
          $message = isset($_GET['m']) ? $_GET['m'] : '';

          // Check if exists
          $stmt = $pdo->prepare("SELECT * FROM device_configuration WHERE device_id = ? AND target_device_id = ?");
          if ($stmt->execute([$_GET['d'], $_GET['td']])) {
            if($stmt->rowCount() > 0) {
              // Determine which fields to update
              $data = $stmt->fetch();
              $spring = isset($spring) ? $spring : $data['spring'];
              $damp = isset($damp) ? $damp : $data['damp'];
              $message = isset($message) ? $message : $data['message'];

              // Update
              $stmt = $pdo->prepare("UPDATE device_configuration SET color = ?, spring = ?, damp = ?, message = ? WHERE device_id = ? AND target_device_id = ?");
            } else {
              // Create
              $stmt = $pdo->prepare("INSERT INTO device_configuration(color, spring, damp, message, device_id, target_device_id) VALUES (?, ?, ?, ?, ?, ?)");
            }
          }
          if ($stmt->execute([$hexcolor, $spring, $damp, $message, $_GET['d'], $_GET['td']])) {
            $response = 1;
          }
        }
      break;
      case 'rdc': // remove device configuration
        if(isset($_GET['td'])) {
          // Check if exists
          $stmt = $pdo->prepare("SELECT * FROM device_configuration WHERE device_id = ? AND target_device_id = ?");
          if ($stmt->execute([$_GET['d'], $_GET['td']])) {
            // Remove all queue items of device and target device
            $stmt = $pdo->prepare("DELETE FROM queue WHERE device_id = ? AND target_device_id = ?");
            if ($stmt->execute([$_GET['d'], $_GET['td']])) {
              // Remove device configuration
              $stmt = $pdo->prepare("DELETE FROM device_configuration WHERE device_id = ? AND target_device_id = ?");
              if ($stmt->execute([$_GET['d'], $_GET['td']])) {
                $response = 1;
              }
            }
          }
        }
      break;
      case 'bdc': // blacklist device configuration
        if(isset($_GET['td']) && isset($_GET['b'])) {
          // Check if exists
          $stmt = $pdo->prepare("UPDATE device_configuration SET blacklist = ? WHERE device_id = ? AND target_device_id = ?");
          if ($stmt->execute([$_GET['b'], $_GET['td'], $_GET['d']])) {
            $response = 1;
          }
        }
      break;
      case 'gqi': // get queue item
          // Get queue item
          $stmt = $pdo->prepare("SELECT * FROM device_configuration WHERE target_device_id = ? AND device_id" .
		        " = (SELECT device_id FROM queue WHERE target_device_id = ? ORDER BY timestamp LIMIT 1)");
          if($stmt->execute([$_GET['d'], $_GET['d']])) {
            if ($stmt->rowCount() == 1) {
              $dc = $stmt->fetch();

              // Delete from queue because it's not needed anymore, delete all from queue when temp
              $stmt = $pdo->prepare("DELETE FROM queue WHERE target_device_id = ? " . ($dc['temp'] != 1 ? 'LIMIT 1' : '') . "");
              if ($stmt->execute([$_GET['d']])) {
                // Return queue item
                // We need this check because workshop 1 hardware isn't compatible with a response of more than the color
                if(isset($_GET['v']) && $_GET['v'] == '2') {
                  $response = $dc['color'] . ',' . $dc['spring'] . ',' . $dc['damp'] . ',' . $dc['message'];
                } else {
                  $response = $dc['color'];
                }

                // A temp device configuration has to be deleted after one queue item has been taken
                // Note: this is a quick fix
                if($dc['temp'] == 1) {
                  $stmt1 = $pdo->prepare("DELETE FROM queue WHERE device_id = ?");
                  if($stmt1->execute([$_GET['d']])) {

                  }

                  // OR doesnt work for some reason
                  $stmt1 = $pdo->prepare("DELETE FROM device_configuration WHERE target_device_id = ? AND temp = ?");
                  if($stmt1->execute([$_GET['d'], 1])) {

                  }
                  $stmt1 = $pdo->prepare("DELETE FROM device_configuration WHERE device_id = ? AND temp = ?");
                  if($stmt1->execute([$_GET['d'], 1])) {

                  }
                }
              } else {

              }
          }
        }
      break;
      case 'sqi': // set queue item
        // Blacklisted device configuration should be able to insert something in the queue
        $stmt = $pdo->prepare("SELECT * FROM device_configuration WHERE device_id = ? AND blacklist = 0");
        if($stmt->execute([$_GET['d']])) {
          $data = $stmt->fetchAll();
          foreach($data as $row) {
            $stmt = $pdo->prepare("INSERT INTO queue(device_id, target_device_id) VALUES (?, ?)");
            if ($stmt->execute([$_GET['d'], $row['target_device_id']])) {
              $response = 1;
            }
          }
        }
      break;
      case 'id': // insert device
      	$stmt = $pdo->prepare("INSERT INTO device(id) VALUES(?)");
      	if($stmt->execute([$_GET['d']])) {
      	  $response = 1;
      	}
      break;
    }



    // Parameter r can be used for redirecting
    if(isset($_GET['r'])) {
      if($_GET['r'] == '') {
        $location = ROOT . '/dashboard.php?d=' . $_GET['d'];
      } else {
        $location = ROOT . $_GET['r'];
      }
      redirect($location);
    } else {
      echo $response;
    }
  }

  // Note: This is a quick fix
  // TODO: Make more efficient
  if(isset($_GET['t']) && $_GET['t'] == 'boom') {
    $stmt1 = $pdo->prepare("SELECT * FROM device");
    if($stmt1->execute()) {
      $devices = $stmt1->fetchAll();
      for($i = 0; $i < count($devices); $i++) {
        for($x = 0; $x < count($devices); $x++) {
          if($devices[$i] != $devices[$x]) {
            // Check if device configuration exists
            $stmt2 = $pdo->prepare("SELECT * FROM device_configuration WHERE device_id = ? AND target_device_id = ?");
            if($stmt2->execute([$devices[$i]['id'], $devices[$x]['id']])) {
              // Insert if it doesn't exist
              if($stmt2->rowCount() == 0) {
                $stmt2 = $pdo->prepare("INSERT INTO device_configuration(device_id, target_device_id, color, spring, damp, temp) VALUES(?, ?, ?, ?, ?, ?)");
                // TODO: color has to be random
                if($stmt2->execute([$devices[$i]['id'], $devices[$x]['id'], randomColor(), 127, 127, 1])) {
                  $response = 1;
                }
              }
              // Insert in queue
              $stmt3 = $pdo->prepare("INSERT INTO queue(device_id, target_device_id) VALUES(?, ?)");
              // TODO: color has to be random
              if($stmt3->execute([$devices[$i]['id'], $devices[$x]['id']])) {
                $response = 1;
              }
            }
          }
        }
      }
      $response = 1;
    }
    echo $response;
  }


?>

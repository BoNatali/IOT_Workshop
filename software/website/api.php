<?php
require_once('config.php');
require_once('util.php');
require_once('database.php');
require_once('query.php');

$query = DatabaseQuery::getAll();

// All possible parameters
$p = [
't' => null, // Type
'd' => null, // Device id
'td' => null, // Target Device Id
'c' => null, // Color
'cv' => null, // Color Value Type
'sc' => null, // Spring Constant
'dc' => null, // Damp Constant
'm' => null, // Message value
'r' => null, // Redirect value
'b' => null, // Blacklist
'r' => null, // Redirect url
'rt' => null // Return type
];

// Bind parameters to query string values
foreach($p as $key => $value) {
    if(array_key_exists($key, $_GET)) {
        $p[$key] = $_GET[$key];
    }
}

// Default response
$response = -1;


// Router
// Device id (d) and type (t) is needed for every operation
if(isset($p['d']) && isset($p['t'])) {
    // Go through all available types of operations
    switch($p['t']) {
        // Set device configuration
        case 'sdc':
            $response = setDeviceConfiguration($p['d'], $p['td'], $p['c'], $p['cv'], $p['sc'], $p['dc'], $p['m']);
            break;
        // Remove device configuration
        case 'rdc':
            $response = removeDeviceConfiguration($p['d'], $p['td']);
            break;
        // Blacklist device configuration
        case 'bdc':
            $response = blacklistDeviceConfiguration($p['d'], $p['td'], $p['b']);
            break;
        // Get queue item
        case 'gqi':
            $response = getQueueItem($p['d'], $p['v']);
            break;
        // Set queue item
        case 'sqi':
            $response = setQueueItem[$p['d']];
            break;
        // Create device
        case 'id':
            $response = createDevice($p['d']);
            break;
        // Mass assign
        case 'ma':
            // $response = massAssign();
            $response = -1;
            break;
}

// Parameter 'r' can be used for redirecting
if(isset($p['r'])) {
    if($p['r'] == '') {
        $location = ROOT . '/dashboard.php?d=' . $p['d'];
    } else {
        $location = ROOT . $p['r'];
    }
    redirect($location);
} else {
    if(isset($p['rt']) && $p['rt'] == 'json') {
        $response = json_encode($response);
    }
    echo $response;
}

}

/**
* Blacklist Device Configuration by providing your device id and the target device id
* @param string $device_id Id of the first device
* @param string $target_device_id Id of the second device
* @param int $blacklist 0 (false) or 1 (true)
* @return int
*/
function blacklistDeviceConfiguration($device_id, $target_device_id, $blacklist) {
    if(isset($device_id) && isset($target_device_id) && isset($blacklist)) {
      $query = DatabaseQuery::getAll();
        // Prepare update device configuration and execute
        $stmt = Database::getInstance()->prepare($query['update_device_configuration_blacklist']);
        if ($stmt->execute([$blacklist, $target_device_id, $device_id])) {
            return 1;
        }
    }
    return -1;
}

/**
* Set Device Configuration between two devices
* @param string $device_id Id of the first device
* @param string $target_device_id Id of the second device
* @param string $color Hex/Hue color
* @param string $color_type Hex or Hue is supported
* @param int $spring_constant Value of the spring (aliveness)
* @param int $damp_constant Value of the damp (laziness)
* @param string $message Can be anything, we used it for sinterklaas message.
* @return int
*/
function setDeviceConfiguration($device_id, $target_device_id, $color, $color_type = null, $spring_constant = null, $damp_constant = null, $message = null) {
    // Check if required values are not null
    if(isset($color) && isset($device_id) && isset($target_device_id)) {
        // When color value is higher than 3 we interpret it as hex
        if(isset($color_type) && $color_type == 'hue') {
            // Convert hue to hex (000000 when invalid)
            $hexcolor = hsl2hex([$color/360, 1, 0.5]);
        } else {
            // Create 6 length hex color if neccesary (000000 when invalid)
            $hexcolor = '#' . validate_hex($color);
        }
        
        // Spring constant (optional)
        if (isset($spring_constant) && is_int($spring_constant)) {
            $spring_constant = round((255 / 100) * $spring_constant);
        }
        // Damp constant (optional)
        if(isset($damp_constant) && is_int($damp_constant)) {
            $damp_constant = round((255 / 100) * $damp_constant);
        }
        // Message (optional)
        if(isset($message) && is_string($message)) {
            $message = $message;
        }
        
        $query = DatabaseQuery::getAll();
        $db = Database::getInstance();
        
        // Check if device configuration exists between device id and target device id, if yes, update it
        $stmt = $db->prepare($query['read_device_configuration']);
        if ($stmt->execute([$device_id, $target_device_id])) {
            if($stmt->rowCount() > 0) {
                // Determine which fields to update
                $data = $stmt->fetch();
                
                // Use database existing value if null
                $spring_constant = isset($spring_constant) ? $spring_constant : $data['spring'];
                $damp_constant = isset($damp_constant) ? $damp_constant : $data['damp'];
                $message = isset($message) ? $message : $data['message'];
                
                // Prepare update statement
                $stmt = $db->prepare($query['update_device_configuration']);
            } else {
                // Prepare create statement
                $stmt = $db->prepare($query['create_device_configuration']);
            }
        }
        // Execute update/create statement
        if ($stmt->execute([$hexcolor, $spring_constant, $damp_constant, $message, $device_id, $target_device_id])) {
            return 1;
        }
    }
    return -1;
}

/**
* Remove Device Configuration between two devices
* @param string $device_id Id of first device
* @param string $target_device_id Id of second device
* @return int
*/
function removeDeviceConfiguration($device_id, $target_device_id) {
    if(isset($device_id) && isset($target_device_id)) {
        $query = DatabaseQuery::getAll();
        $db = Database::getInstance();
        
        // Check if exists
        $stmt = $db->prepare($query['read_device_configuration']);
        if ($stmt->execute([$device_id, $target_device_id])) {
            if($stmt->rowCount() > 0) {
                // Remove all queue items of device and target device
                $stmt = $db->prepare($query['delete_queue_items']);
                if ($stmt->execute([$device_id, $target_device_id])) {
                    // Remove device configuration
                    $stmt = $db->prepare($query['delete_device_configuration']);
                    if ($stmt->execute([$device_id, $target_device_id])) {
                        return 1;
                    }
                }
            }
        }
    }
    return -1;
}

/**
* Get an item from the queue
* @param string $device_id Id of device
* @param string $version Version of response (1=only color, 2=color,spring,constant,message)
* @return string/int (if string: values devided with commas)
*/
function getQueueItem($device_id, $version = null) {
    $response = -1;
    if(isset($device_id)) {
        $query = DatabaseQuery::getAll();
        $stmt = Database::getInstance()->prepare($query['read_queue_item']);
        if($stmt->execute([$device_id, $device_id])) {
            if ($stmt->rowCount() == 1) {
                $dc = $stmt->fetch();
                
                // Delete from queue because it's not needed anymore, delete all from queue when temp
                if($dc['temp'] != 1) {
                  $q = $query['delete_queue_item_target_device_id_limit'];
                } else {
                  $q = $query['delete_queue_item_target_device_id'];
                }

                $stmt = Database::getInstance()->prepare($q);
                if ($stmt->execute([$device_id])) {
                    // Return queue item
                    // We need this check because workshop 1 hardware isn't compatible with a response of more than the color
                    if(isset($version) && $version == '2') {
                        $response = $dc['color'] . ',' . $dc['spring'] . ',' . $dc['damp'] . ',' . $dc['message'];
                    } else {
                        $response = $dc['color'];
                    }
                    
                    // A temp device configuration has to be deleted after one queue item has been taken
                    if($dc['temp'] == 1) {
                        $stmt1 = Database::getInstance()->prepare($query['delete_queue_item']);
                        if(!$stmt1->execute([$device_id])) {
                            
                        }
                        
                        // OR doesnt work for some reason
                        $stmt1 = Database::getInstance()->prepare($query['delete_device_configuration_where_target_device_id_and_temp']);
                        if(!$stmt1->execute([$device_id, 1])) {
                            
                        }
                        $stmt1 = Database::getInstance()->prepare($query['delete_device_configuration_where_device_id_and_temp']);
                        if(!$stmt1->execute([$device_id, 1])) {
                            
                        }
                    }
                }
            }
        }
    }
    return $response;
}

/**
* Create a device by providing an id (most of the times 4 chars long)
* @param string $device_id Id of the device
* @return int
*/
function createDevice($device_id) {
    if(isset($device_id)) {
        $query = DatabaseQuery::getAll();
        $stmt = Database::getInstance()->prepare($query['create_device']);
        if($stmt->execute([$device_id])) {
            return 1;
        }
    }
    return -1;
}
/**
* Insert an item in the queue by providing your device id
* @param string device_id Id of the device
* @return int
*/
function setQueueItem($device_id) {
    $response = -1;
    if(isset($device_id)) {
        $query = DatabaseQuery::getAll();
        $db = Database::getInstance();
        // Insert queue items based on howmany device configurations (links between devices) there are
        $stmt =  $db->prepare($query['list_device_configuration']);
        if($stmt->execute([$device_id])) {
            $data = $stmt->fetchAll();
            // Create one item in the queue for each link
            foreach($data as $row) {
                $stmt = $db->prepare($query['create_queue_item']);
                if ($stmt->execute([$device_id, $row['target_device_id']])) {
                    $response = 1;
                }
                
            }
        }
        
    }
    return $response;
}

/**
* (EXPERIMENTAL) Create a temporary device configuration link for each device available and insert a queue item
* This was created in a short period of time and isn't production ready
* @return int
*/
function massAssign() {
    $stmt1 = $pdo->prepare($query['list_device']);
    if($stmt1->execute()) {
        $devices = $stmt1->fetchAll();
        for($i = 0; $i < count($devices); $i++) {
            for($x = 0; $x < count($devices); $x++) {
                if($devices[$i] != $devices[$x]) {
                    // Check if device configuration exists
                    $stmt2 = $pdo->prepare($query['read_device_configuration']);
                    if($stmt2->execute([$devices[$i]['id'], $devices[$x]['id']])) {
                        // Insert if it doesn't exist
                        if($stmt2->rowCount() == 0) {
                            $stmt2 = $pdo->prepare($query['create_device_configuration_with_temp']);
                            // TODO: color has to be random
                            if($stmt2->execute([$devices[$i]['id'], $devices[$x]['id'], randomColor(), 127, 127, 1])) {
                                $response = 1;
                            }
                        }
                        // Insert in queue
                        $stmt3 = $pdo->prepare($query['create_queue_item']);
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
    return $response;
}

?>
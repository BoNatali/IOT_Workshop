<?php
    require_once('database.php');

    /*
    * You can create models for each table or create functions for each query
    */
    class DatabaseQuery {
        /**
        * Prepares and executes query
        * @param string $query
        * @param array $parameters
        * @return boolean / PDOStatement
        */
        public static function prepareAndExecute($query, $parameters) {
            $stmt = Database::getInstance()->prepare($query);
            if($stmt->execute($parameters)) {
                return $stmt;
            } else {
                return false;
            }
        }

        /**
        * Create Device Configuration
        * @param string $device_id
        * @param string $target_device_id
        * @param string $color
        * @param int $spring_constant
        * @param int $damp_constant
        * @param string $message
        * @return boolean / PDOStatement
        */
        public static function createDeviceConfiguration($color, $spring_constant, $damp_constant, $message, $device_id, $target_device_id) {
            return self::prepareAndExecute('INSERT INTO device_configuration(color, spring, damp, message, device_id, target_device_id) VALUES (?, ?, ?, ?, ?, ?)',
            [$color, $spring_constant, $damp_constant, $message, $device_id, $target_device_id]);
        }

        /**
        * Create Device Configuration With Temp
        * @param string $device_id
        * @param string $target_device_id
        * @param string $color
        * @param int $spring_constant
        * @param int $damp_constant
        * @param int $temp
        * @return boolean / PDOStatement
        */
        public static function createDeviceConfigurationWithTemp($device_id, $target_device_id, $color, $spring_constant, $damp_constant, $temp) {
            return self::prepareAndExecute('INSERT INTO device_configuration(device_id, target_device_id, color, spring, damp, temp) VALUES(?, ?, ?, ?, ?, ?)',
            [$device_id, $target_device_id, $color, $spring_constant, $damp_constant, $temp]);
        }

        /**
        * Read Device Configuration
        * @param string $device_id
        * @param string $target_device_id
        * @return boolean / PDOStatement
        */
        public static function readDeviceConfiguration($device_id, $target_device_id) {
            return self::prepareAndExecute('SELECT * FROM device_configuration WHERE device_id = ? AND target_device_id = ?',
            [$device_id, $target_device_id]);
        }

        /**
        * Read Device Configuration With Message
        * @param string $device_id
        * @param string $target_device_id
        * @return boolean / PDOStatement
        */
        public static function readDeviceConfigurationWithMessage($device_id, $target_device_id) {
            return self::prepareAndExecute('SELECT message FROM device_configuration WHERE device_id = ? AND target_device_id = ?',
            [$device_id, $target_device_id]);
        }

        /**
        * Update Device Configuration
        * @param string $device_id
        * @param string $target_device_id
        * @param string $color
        * @param int $spring_constant
        * @param int $damp_constant
        * @param string $message
        * @return boolean / PDOStatement
        */
        public static function updateDeviceConfiguration($color, $spring_constant, $damp_constant, $message, $device_id, $target_device_id) {
            return self::prepareAndExecute('UPDATE device_configuration SET color = ?, spring = ?, damp = ?, message = ? WHERE device_id = ? AND target_device_id = ?',
            [$color, $spring_constant, $damp_constant, $message, $device_id, $target_device_id]);
        }

        /**
        * Update Device Configuration With Blacklist
        * @param int $blacklist
        * @param string $device_id
        * @param string $target_device_id
        * @return boolean / PDOStatement
        */
        public static function updateDeviceConfigurationWithBlacklist($blacklist, $device_id, $target_device_id) {
            return self::prepareAndExecute('UPDATE device_configuration SET blacklist = ? WHERE device_id = ? AND target_device_id = ?',
            [$blacklist, $device_id, $target_device_id]);
        }
        
        /**
        * Delete Device Configuration
        * @param string $device_id
        * @param string $target_device_id
        * @return boolean / PDOStatement
        */
        public static function deleteDeviceConfiguration($device_id, $target_device_id) {
            return self::prepareAndExecute('DELETE FROM device_configuration WHERE device_id = ? AND target_device_id = ?',
            [$device_id, $target_device_id]);
        }

        /**
        * Delete Device Configuration With Target Device Id And Temp
        * @param string $target_device_id
        * @param string $temp
        * @return boolean / PDOStatement
        */
        public static function deleteDeviceConfigurationWithTargetDeviceIdAndTemp($target_device_id, $temp) {
            return self::prepareAndExecute('DELETE FROM device_configuration WHERE target_device_id = ? AND temp = ?',
            [$target_device_id, $temp]);
        }

        /**
        * Delete Device Configuration With Device Id And Temp
        * @param string $device_id
        * @param string $temp
        * @return boolean / PDOStatement
        */
        public static function deleteDeviceConfigurationWithDeviceIdAndTemp($device_id, $temp) {
            return self::prepareAndExecute('DELETE FROM device_configuration WHERE device_id = ? AND temp = ?',
            [$device_id, $temp]);
        }

        /**
        * List Device Configuration
        * @param string $device_id
        * @return boolean / PDOStatement
        */
        public static function listDeviceConfiguration($device_id) {
            return self::prepareAndExecute('SELECT * FROM device_configuration WHERE device_id = ? AND blacklist = 0',
            [$device_id]);
        }

        /**
        * List Device Configuration Not Temp
        * @param string $device_id
        * @return boolean / PDOStatement
        */
        public static function listDeviceConfigurationNotTemp($device_id) {
            return self::prepareAndExecute('SELECT * FROM device_configuration WHERE device_id = ? AND temp = 0',
            [$device_id]);
        }

        /**
        * List Device Configuration With Target Device Id And Not Temp
        * @param string $device_id
        * @return boolean / PDOStatement
        */
        public static function listDeviceConfigurationWithTargetDeviceIdAndNotTemp($target_device_id) {
            return self::prepareAndExecute('SELECT * FROM device_configuration WHERE target_device_id = ? AND temp = 0',
            [$target_device_id]);
        }

        /**
        * Create Queue Item
        * @param string $device_id
        * @param string $target_device_id
        * @return boolean / PDOStatement
        */
        public static function createQueueItem($device_id, $target_device_id) {
            return self::prepareAndExecute('INSERT INTO queue(device_id, target_device_id) VALUES (?, ?)',
            [$device_id, $target_device_id]);
        }

        /**
        * Read Queue Item
        * @param string $target_device_id
        * @param string $target_device_id_2
        * @return boolean / PDOStatement
        */
        public static function readQueueItem($target_device_id, $target_device_id_2) {
            return self::prepareAndExecute('SELECT * FROM device_configuration WHERE target_device_id = ? AND device_id = (SELECT device_id FROM queue WHERE target_device_id = ? ORDER BY timestamp LIMIT 1)',
            [$target_device_id, $target_device_id_2]);
        }

        /**
        * Delete Queue Items
        * @param string $device_id
        * @param string $target_device_id
        * @return boolean / PDOStatement
        */
        public static function deleteQueueItems($device_id, $target_device_id) {
            return self::prepareAndExecute('DELETE FROM queue WHERE device_id = ? AND target_device_id = ?',
            [$device_id, $target_device_id]);
        }

        /**
        * Delete Queue Item
        * @param string $device_id
        * @return boolean / PDOStatement
        */
        public static function deleteQueueItem($device_id) {
            return self::prepareAndExecute('DELETE FROM queue WHERE device_id = ?',
            [$device_id]);
        }

        /**
        * Delete Queue Item With Target Device Id And Limit
        * @param string $target_device_id
        * @return boolean / PDOStatement
        */
        public static function deleteQueueItemWithTargetDeviceIdAndLimit($target_device_id) {
            return self::prepareAndExecute('DELETE FROM queue WHERE target_device_id = ? LIMIT 1',
            [$target_device_id]);
        }

        /**
        * Delete Queue Item With Target Device Id
        * @param string $target_device_id
        * @return boolean / PDOStatement
        */
        public static function deleteQueueItemWithTargetDeviceId($target_device_id) {
            return self::prepareAndExecute('DELETE FROM queue WHERE target_device_id = ?',
            [$target_device_id]);
        }

        /**
        * Create Device
        * @param string $id
        * @return boolean / PDOStatement
        */
        public static function createDevice($id) {
            return self::prepareAndExecute('INSERT INTO device(id) VALUES(?)',
            [$id]);
        }

        /**
        * Read Device
        * @param string $id
        * @return boolean / PDOStatement
        */
        public static function readDevice($id) {
            return self::prepareAndExecute('SELECT * FROM device WHERE id = ?',
            [$id]);
        }

        /**
        * List Device
        * @return boolean / PDOStatement
        */
        public static function listDevice() {
            return self::prepareAndExecute('SELECT * FROM device',
            []);
        }
    }
?>
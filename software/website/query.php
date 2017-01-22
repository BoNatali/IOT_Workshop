<?php
    /*
    * You can create models for each table or create functions for each query
    */
    class DatabaseQuery {
        public static function getAll() {
            return [
                'create_device_configuration' => 'INSERT INTO device_configuration(color, spring, damp, message, device_id, target_device_id) VALUES (?, ?, ?, ?, ?, ?)',
                'create_device_configuration_with_temp' => 'INSERT INTO device_configuration(device_id, target_device_id, color, spring, damp, temp) VALUES(?, ?, ?, ?, ?, ?)',

                'read_device_configuration' => 'SELECT * FROM device_configuration WHERE device_id = ? AND target_device_id = ?',
                'read_device_configuration_message' => 'SELECT message FROM device_configuration WHERE device_id = ? AND target_device_id = ?',

                'update_device_configuration' => 'UPDATE device_configuration SET color = ?, spring = ?, damp = ?, message = ? WHERE device_id = ? AND target_device_id = ?',
                'update_device_configuration_blacklist' => 'UPDATE device_configuration SET blacklist = ? WHERE device_id = ? AND target_device_id = ?',

                'delete_device_configuration' => 'DELETE FROM device_configuration WHERE device_id = ? AND target_device_id = ?',
                'delete_device_configuration_where_target_device_id_and_temp' => 'DELETE FROM device_configuration WHERE target_device_id = ? AND temp = ?',
                'delete_device_configuration_where_device_id_and_temp' => 'DELETE FROM device_configuration WHERE device_id = ? AND temp = ?',

                'list_device_configuration' => 'SELECT * FROM device_configuration WHERE device_id = ? AND blacklist = 0',
                'list_device_configuration_not_temp' => 'SELECT * FROM device_configuration WHERE device_id = ? AND temp = 0',
                'list_device_configuration_where_target_device_id_and_not_temp' => 'SELECT * FROM device_configuration WHERE target_device_id = ? AND temp = 0',

                'create_queue_item' => 'INSERT INTO queue(device_id, target_device_id) VALUES (?, ?)',

                'read_queue_item' => 'SELECT * FROM device_configuration WHERE target_device_id = ? AND device_id = (SELECT device_id FROM queue WHERE target_device_id = ? ORDER BY timestamp LIMIT 1)',

                'delete_queue_items' => 'DELETE FROM queue WHERE device_id = ? AND target_device_id = ?',
                'delete_queue_item' => 'DELETE FROM queue WHERE device_id = ?',
                'delete_queue_item_target_device_id_limit' => 'DELETE FROM queue WHERE target_device_id = ? LIMIT 1',
                'delete_queue_item_target_device_id' => 'DELETE FROM queue WHERE target_device_id = ?',

                'create_device' => 'INSERT INTO device(id) VALUES(?)',
                'read_device' => 'SELECT * FROM device WHERE id = ?',
                'list_device' => 'SELECT * FROM device'
            ];
        }
    }
?>
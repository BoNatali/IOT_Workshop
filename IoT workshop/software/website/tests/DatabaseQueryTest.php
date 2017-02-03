<?php
require_once('../query.php');

/*
* This file covers most use cases for the DatabaseQueries
*/

use PHPUnit\Framework\TestCase;

class DatabaseQueryTest extends TestCase
{
    /**
    * Create Device Configuration
    * @covers DatabaseQuery::createDeviceConfiguration
    */
    public function testCreateDeviceConfiguration() {
        DatabaseQuery::deleteDeviceConfiguration('t111', 't111');

        $this->assertNotFalse(DatabaseQuery::createDeviceConfiguration('#fff333', 128, 128, 'Test Message', 't111', 't111'));
    }
    
    /**
    * Create Device Configuration With Temp
    * @covers DatabaseQuery::createDeviceConfigurationWithTemp
    */
    public function testCreateDeviceConfigurationWithTemp() {
        DatabaseQuery::deleteDeviceConfiguration('t111', 't111');

        $this->assertNotFalse(DatabaseQuery::createDeviceConfigurationWithTemp('t111', 't111', '#020202', 128, 128, 1));
    }
    
    /**
    * Read Device Configuration
    * @covers DatabaseQuery::readDeviceConfiguration
    */
    public function testReadDeviceConfiguration() {
        $this->assertNotFalse(DatabaseQuery::readDeviceConfiguration('t111', 't111'));
    }
    
    /**
    * Read Device Configuration With Message
    * @covers DatabaseQuery::readDeviceConfigurationWithMessage
    */
    public function testReadDeviceConfigurationWithMessage() {
        $this->assertNotFalse(DatabaseQuery::readDeviceConfigurationWithMessage('t111', 't111'));
    }
    
    /**
    * Update Device Configuration
    * @covers DatabaseQuery::updateDeviceConfiguration
    */
    public function testUpdateDeviceConfiguration() {
        $this->assertNotFalse(DatabaseQuery::updateDeviceConfiguration('#324576', 128, 128, 'Test Message', 't111', 't111'));
    }
    
    /**
    * Update Device Configuration With Blacklist
    * @covers DatabaseQuery::updateDeviceConfigurationWithBlacklist
    */
    public function testUpdateDeviceConfigurationWithBlacklist() {
        $this->assertNotFalse(DatabaseQuery::updateDeviceConfigurationWithBlacklist(1, 't111', 't11'));
    }
    
    /**
    * Delete Device Configuration
    * @covers DatabaseQuery::deleteDeviceConfiguration
    */
    public function testDeleteDeviceConfiguration() {
        $this->assertNotFalse(DatabaseQuery::deleteDeviceConfiguration('t111', 't111'));
    }
    
    /**
    * Delete Device Configuration With Target Device Id And Temp
    * @covers DatabaseQuery::deleteDeviceConfigurationWithTargetDeviceIdAndTemp
    */
    public function testDeleteDeviceConfigurationWithTargetDeviceIdAndTemp() {
        $this->assertNotFalse(DatabaseQuery::deleteDeviceConfigurationWithTargetDeviceIdAndTemp('t111', 1));
    }
    
    /**
    * Delete Device Configuration With Device Id And Temp
    * @covers DatabaseQuery::deleteDeviceConfigurationWithDeviceIdAndTemp
    */
    public function testDeleteDeviceConfigurationWithDeviceIdAndTemp() {
        $this->assertNotFalse(DatabaseQuery::deleteDeviceConfigurationWithDeviceIdAndTemp('t111', 1));
    }
    
    /**
    * List Device Configuration
    * @covers DatabaseQuery::listDeviceConfiguration
    */
    public function testListDeviceConfiguration() {
        $this->assertNotFalse(DatabaseQuery::listDeviceConfiguration('t111'));
    }
    
    /**
    * List Device Configuration Not Temp
    * @covers DatabaseQuery::listDeviceConfigurationNotTemp
    */
    public function testListDeviceConfigurationNotTemp() {
        $this->assertNotFalse(DatabaseQuery::listDeviceConfigurationNotTemp('t111'));
    }
    
    /**
    * List Device Configuration With Target Device Id And Not Temp
    * @covers DatabaseQuery::listDeviceConfigurationWithTargetDeviceIdAndNotTemp
    */
    public function testListDeviceConfigurationWithTargetDeviceIdAndNotTemp() {
        $this->assertNotFalse(DatabaseQuery::listDeviceConfigurationWithTargetDeviceIdAndNotTemp('t111'));
    }
    
    /**
    * Create Queue Item
    * @covers DatabaseQuery::createQueueItem
    */
    public function testCreateQueueItem() {
        DatabaseQuery::createDeviceConfiguration('#fff333', 128, 128, 'Test Message', 't111', 't111');

        $this->assertNotFalse(DatabaseQuery::createQueueItem('t111', 't111'));
    }
    
    /**
    * Read Queue Item
    * @covers DatabaseQuery::readQueueItem
    */
    public function testReadQueueItem() {
        $this->assertNotFalse(DatabaseQuery::readQueueItem('t111', 't111'));
    }
    
    /**
    * Delete Queue Items
    * @covers DatabaseQuery::deleteQueueItems
    */
    public function testDeleteQueueItems() {
        $this->assertNotFalse(DatabaseQuery::deleteQueueItems('t111', 't111'));
    }
    
    /**
    * Delete Queue Item
    * @covers DatabaseQuery::deleteQueueItem
    */
    public function testDeleteQueueItem() {
        $this->assertNotFalse(DatabaseQuery::deleteQueueItem('t111'));
    }
    
    /**
    * Delete Queue Item With Target Device Id And Limit
    * @covers DatabaseQuery::deleteQueueItemWithTargetDeviceIdAndLimit
    */
    public function testDeleteQueueItemWithTargetDeviceIdAndLimit() {
        $this->assertNotFalse(DatabaseQuery::deleteQueueItemWithTargetDeviceIdAndLimit('t111'));
    }
    
    /**
    * Delete Queue Item With Target Device Id
    * @covers DatabaseQuery::deleteQueueItemWithTargetDeviceId
    */
    public function testDeleteQueueItemWithTargetDeviceId() {
        $this->assertNotFalse(DatabaseQuery::deleteQueueItemWithTargetDeviceId('t111'));
    }
    
    /**
    * Create Device
    * @covers DatabaseQuery::createDevice
    */
    public function testCreateDevice() {
        $test_device_id = 't123';

        $this->assertNotFalse(DatabaseQuery::createDevice($test_device_id));

        DatabaseQuery::prepareAndExecute('DELETE FROM device WHERE id = ?', [$test_device_id]);
    }
    
    /**
    * Read Device
    * @covers DatabaseQuery::readDevice
    */
    public function testReadDevice() {
        $this->assertNotFalse(DatabaseQuery::readDevice('t111'));
    }
    
    /**
    * List Device
    * @covers DatabaseQuery::listDevice
    */
    public function testListDevice() {
        $this->assertNotFalse(DatabaseQuery::listDevice());
    }
}
?>
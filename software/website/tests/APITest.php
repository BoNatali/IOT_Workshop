<?php
  require_once('../api.php');

/*
 * This file covers most use cases for the public API
 */

use PHPUnit\Framework\TestCase;

class APITest extends TestCase
{
    /**
     * @before
     */
    public function setupSomeFixtures()
    {
        createDevice('t111');
        setDeviceConfiguration('t111', 't111', '666');
        setQueueItem('t111');
    }

    /*
     * Create Device Fails Without Device Id
     * @covers ::createDevice
     */
    public function testCreateDeviceFailsWithoutDeviceId() {
        $this->assertEquals(-1, createDevice(null));
    }
    /* 
     * Set Device Configuration Fails Without Device Id
     * @covers ::setDeviceConfiguration
     */
    public function testSetDeviceConfigurationFailsWithoutDeviceId()
    {
        $this->assertEquals(-1, setDeviceConfiguration(null, 't111', '666'));
    }
    /*
     * Set Device Configuration Fails With Invalid Device Id
     * @covers ::setDeviceConfiguration4
     */
    public function testSetDeviceConfigurationFailsWithInvalidDeviceId()
    {
        $this->assertEquals(-1, setDeviceConfiguration('t11', 't111', '666'));
    }
    /* 
     * Set Device Configuration Fails Without Target Device Id
     * @covers ::setDeviceConfiguration
     */
    public function testSetDeviceConfigurationFailsWithoutTargetDeviceId()
    {
        $this->assertEquals(-1, setDeviceConfiguration('t111', null, '666'));
    }
    /* 
     * Set Device Configuration Fails With Invalid Target Device Id
     * @covers ::setDeviceConfiguration
     */
    public function testSetDeviceConfigurationFailsWithInvalidTargetDeviceId()
    {
        $this->assertEquals(-1, setDeviceConfiguration('t111', 't11', '666'));
    }
    /*
     * Set Device Configuration Fails Without Color
     * @covers ::setDeviceConfiguration
     */
    public function testSetDeviceConfigurationFailsWithoutColor()
    {
        $this->assertEquals(-1, setDeviceConfiguration('t111', 't111', null));
    }
    /*
     * Set Device Configuration Success With Invalid Color
     * @covers ::setDeviceConfiguration
     */
    public function testSetDeviceConfigurationSuccessWithInvalidColor()
    {
        $this->assertEquals(1, setDeviceConfiguration('t111', 't111', '2'));
    }
    /* 
     * Set Device Configuration Successfull With Valid Device Id And Target Device Id And Color
     * @covers ::setDeviceConfiguration
     */
    public function testSetDeviceConfigurationSuccessWithValidDeviceIdAndTargetDeviceIdAndColor()
    {
        $this->assertEquals(1, setDeviceConfiguration('t111', 't111', '222'));
    }
    /* 
     * Set Device Configuration Successfull With Invalid Spring Constant
     * @covers ::setDeviceConfiguration
     */
    public function testSetDeviceConfigurationSuccessWithInvalidSpringConstant()
    {
        $this->assertEquals(1, setDeviceConfiguration('t111', 't111', '222', 'hex', 20.5, 30));
    }
    /*
     * Set Device Configuration Successfull With Invalid Damp Constant
     * @covers ::setDeviceConfiguration
     */
    public function testSetDeviceConfigurationSuccessWithInvalidDampConstant()
    {
        $this->assertEquals(1, setDeviceConfiguration('t111', 't111', '222', 'hex', 20, 30.5));
    }
    /*
     * Set Queue Item Fails Without Device Id
     * @covers ::setQueueItem
     */
    public function testSetQueueItemFailsWithoutDeviceId()
    {
        $this->assertEquals(-1, setQueueItem(null));
    }
    /* 
     * Set Queue Item Fails With Invalid Device Id
     * @covers ::setQueueItem
     */
    public function testSetQueueItemFailsWithInvalidDeviceId()
    {
        $this->assertEquals(-1, setQueueItem('t11'));
    }
    /* 
     * Set Queue Item Success With Valid Device Id
     * @covers ::setQueueItem
     */
    public function testSetQueueItemSuccessWithValidDeviceId()
    {
        // Delay of 1 second because timestamp is part of primary key
        sleep(1);
        $this->assertEquals(1, setQueueItem('t111'));
    }
    /* 
     * Get Queue Item Fails Without Device Id
     * @covers ::getQueueItem
     */
    public function testGetQueueItemFailsWithoutDeviceId()
    {
        $this->assertEquals(-1, getQueueItem(null));
    }
    /* 
     * Get Queue Item Fails With Invalid Device Id
     * @covers ::getQueueItem
     */
    public function testGetQueueItemFailsWithInvalidDeviceId()
    {
        $this->assertEquals(-1, getQueueItem('t11'));
    }
    /*
     * Get Queue Item Success With Valid Device Id
     * @covers ::getQueueItem
     */
    public function testGetQueueItemSuccessWithValidDeviceId()
    {
        $this->assertTrue(substr_count(getQueueItem('t111'), ',') == 0);
    }
    /* Get Queue Item Success With Valid Device Id And Version Two
     * @covers ::getQueueItem
     */
    public function testGetQueueItemSuccessWithValidDeviceIdAndVersion()
    {
        $this->assertTrue(substr_count(getQueueItem('t111', '2'), ',') > 2);
    }
    /* 
     * Get Queue Item Success With Valid Device Id And Version
     * @covers ::getQueueItem
     */
    public function testGetQueueItemSuccessWithValidDeviceIdAndInvalidVersion()
    {
        $this->assertTrue(substr_count(getQueueItem('t111', 3), ',') == 0);
    }
    /*
     * Remove Device Configuration Fails Without Device Id
     * @covers ::removeDeviceConfiguration
     */
    public function testRemoveDeviceConfigurationFailsWithoutDeviceId()
    {
        $this->assertEquals(-1, removeDeviceConfiguration(null, 't111', '222'));
    }
    /*
     * Remove Device Configuration Fails With Invalid Device Id
     * @covers ::removeDeviceConfiguration
     */
    public function testRemoveDeviceConfigurationFailsWithInvalidDeviceId()
    {
        $this->assertEquals(-1, removeDeviceConfiguration('t11', 't111', '222'));
    }
    /*
     * Remove Device Configuration Fails Without Target Device Id
     * @covers ::removeDeviceConfiguration
     */
    public function testRemoveDeviceConfigurationFailsWithoutTargetDeviceId()
    {
        $this->assertEquals(-1, removeDeviceConfiguration('t111', null, '222'));
    }
     /*
     * Remove Device Configuration Fails With Invalid Target Device Id
     * @covers ::removeDeviceConfiguration
     */
    public function testRemoveDeviceConfigurationFailsWithInvalidTargetDeviceId()
    {
        $this->assertEquals(-1, removeDeviceConfiguration('t111', 't11', '222'));
    }
    /*
     * Remove Device Configuration Successfull With Valid Device Id And Target Device Id
     * @covers ::removeDeviceConfiguration
     */
    public function testRemoveDeviceConfigurationSuccessWithValidDeviceIdAndTargetDeviceId()
    {
        $this->assertEquals(1, removeDeviceConfiguration('t111', 't111', '222'));
    }
}
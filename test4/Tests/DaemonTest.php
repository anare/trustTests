<?php

use PHPUnit\Framework\TestCase;
use Services\Daemon;
use Services\SchedulerManager;

/**
 * @property Daemon daemon
 */
class DaemonTest extends TestCase
{
    public function setUp()
    {
        $this->daemon = new Daemon(new SchedulerManager());
    }

    public function testIsLock()
    {
        $this->daemon->unlock();
        $this->daemon->lock();
        $this->assertNotEquals(false, $this->daemon->isLock());
    }

    public function testAddTask()
    {
        $result = $this->daemon->addTask('+1 seconds,php://task');
        $this->assertNotEquals(false, $result);
    }

    public function testLock()
    {
        $this->daemon->unlock();
        $this->assertGreaterThan(0, $this->daemon->lock());
    }

    public function testUnlock()
    {
        $this->daemon->unlock();
        $this->daemon->lock();
        $this->assertEquals(true, $this->daemon->unlock());
    }
}

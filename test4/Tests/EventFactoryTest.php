<?php

use PHPUnit\Framework\TestCase;
use Services\EventFactory;

class EventFactoryTest extends TestCase
{

    public function testFactory()
    {
        $event = EventFactory::factory(new \DateTime('+1 seconds'), 'php://task');
        $this->assertEquals('task', $event->getTask());
    }
}

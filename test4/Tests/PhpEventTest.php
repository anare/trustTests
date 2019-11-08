<?php

use PHPUnit\Framework\TestCase;
use Services\EventFactory;
use Services\PhpEvent;

class PhpEventTest extends TestCase
{

    public function testExecute()
    {
        $event = EventFactory::factory(new \DateTime('+1 seconds'), 'php://task');
        $this->assertInstanceOf(PhpEvent::class, $event);
    }
}

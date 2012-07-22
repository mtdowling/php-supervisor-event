<?php

namespace Mtdowling\Tests\Supervisor;

use Mtdowling\Supervisor\EventNotification;

/**
 * @author Michael Dowling <michael@guzzlephp.org>
 * @covers Mtdowling\Supervisor\EventNotification
 */
class EventNotificationTest extends \PHPUnit_Framework_TestCase
{
    public function testParsesDataString()
    {
        $data = EventNotification::parseData('ver:3.0 server:supervisor serial:313 pool:event_listener poolserial:313 eventname:TICK_5 len:15');
        $this->assertEquals(array(
            'eventname'  => 'TICK_5',
            'len'        => '15',
            'pool'       => 'event_listener',
            'poolserial' => '313',
            'serial'     => '313',
            'server'     => 'supervisor',
            'ver'        => '3.0'
        ), $data);
    }

    public function testCreatesDataObjectFromText()
    {
        $event = new EventNotification(
            'ver:3.0 server:supervisor serial:313 pool:event_listener poolserial:313 eventname:TICK_5 len:15',
            'when:1313021995',
            array(
                'eventname'  => 'TICK_5',
                'len'        => '15',
                'pool'       => 'event_listener',
                'poolserial' => '313',
                'serial'     => '313',
                'server'     => 'supervisor',
                'ver'        => '3.0'
            )
        );

        $this->assertEquals('TICK_5', $event->getEventName());
        $this->assertEquals(15, $event->getLen());
        $this->assertEquals('event_listener', $event->getPool());
        $this->assertEquals('313', $event->getPoolSerial());
        $this->assertEquals('313', $event->getSerial());
        $this->assertEquals('supervisor', $event->getServer());
        $this->assertEquals('3.0', $event->getVer());
        $this->assertEquals('when:1313021995', $event->getBody());
        $this->assertEquals('1313021995', $event->getData('when'));
        $this->assertInternalType('array', $event->getData());
        $this->assertInternalType('null', $event->getData('wfwewewfe'));
        $this->assertEquals("ver:3.0 server:supervisor serial:313 pool:event_listener poolserial:313 eventname:TICK_5 len:15\nwhen:1313021995", (string) $event);
    }

    public function testHandlesMultiLineUpdates()
    {
        $event = new EventNotification(
            'ver:3.0 server:supervisor serial:313 pool:event_listener poolserial:313 eventname:TICK_5 len:15',
            "when:1313021995\nthis is data",
            array(
                'eventname' => 'TICK_5',
                'len'       => '28'
            )
        );

        $this->assertEquals('1313021995', $event->getData('when'));
        $this->assertEquals('this is data', $event->getBody());
    }
}

<?php

namespace Supervisor\Tests;

use Supervisor\EventListener;
use Supervisor\EventNotification;

/**
 * @author Michael Dowling <michael@guzzlephp.org>
 */
class EventListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Supervisor\EventListener::setInputStream
     * @covers Supervisor\EventListener::setOutputStream
     * @covers Supervisor\EventListener::setErrorStream
     * @covers Supervisor\EventListener::log
     * @covers Supervisor\EventListener::readLine
     * @covers Supervisor\EventListener::sendBusy
     * @covers Supervisor\EventListener::sendAcknowledged
     * @covers Supervisor\EventListener::sendComplete
     * @covers Supervisor\EventListener::sendFail
     * @covers Supervisor\EventListener::sendReady
     */
    public function testUsesCustomStreams()
    {
        $listener = new EventListener();
        $es = fopen('php://memory', 'w+');
        $is = fopen('php://memory', 'w+');
        $os = fopen('php://memory', 'w+');
        $listener->setErrorStream($es)
            ->setOutputStream($os)
            ->setInputStream($is);

        // Ensure that the output is sent to the output stream
        $listener->sendBusy();
        $listener->sendAcknowledged();
        $listener->sendComplete();
        $listener->sendFail();
        $listener->sendReady();
        rewind($os);
        $this->assertEquals("BUSY\nACKNOWLEDGED\nRESULT 2\nOKRESULT 4\nFAILREADY\n", stream_get_contents($os));

        // Ensure that log messages are sent to the error stream
        $listener->log('Test');
        rewind($es);
        $this->assertContains('Test', fgets($es));

        // Ensure that input is read from the input stream
        fwrite($is, 'test');
        rewind($is);
        $this->assertEquals('test', $listener->readLine());

        fclose($es);
        fclose($os);
        fclose($is);
    }

    /**
     * @covers Supervisor\EventListener::listen
     */
    public function testListensForEvents()
    {
        $listener = new EventListener();
        $es = fopen('php://memory', 'w+');
        $is = fopen('php://memory', 'w+');
        $os = fopen('php://memory', 'w+');
        $listener->setErrorStream($es)
            ->setOutputStream($os)
            ->setInputStream($is);

        // Make sure blank lines are ignored
        fwrite($is, "\n");
        fwrite($is, "ver:3.0 server:supervisor serial:313 pool:event_listener poolserial:313 eventname:TICK_5 len:15\n");
        fwrite($is, "when:1313021985");
        fwrite($is, "ver:3.0 server:supervisor serial:313 pool:event_listener poolserial:313 eventname:TICK_60 len:15\n");
        fwrite($is, "when:1313021990");
        fwrite($is, "ver:3.0 server:supervisor serial:313 pool:event_listener poolserial:313 eventname:TICK_3600 len:15\n");
        fwrite($is, "when:1313021995");
        rewind($is);

        $total = 0;
        $listener->listen(function(EventListener $listener, EventNotification $event) use (&$total) {
            $listener->log((string) $event);
            if (++$total == 1) {
                return true;
            } else if ($total == 2) {
                return false;
            } else {
                return 'quit';
            }
        });
        
        rewind($es);
        rewind($os);

        // Ensure that the correct output was given
        $this->assertEquals("READY\nRESULT 2\nOKREADY\nRESULT 4\nFAILREADY\n", stream_get_contents($os));
        // Ensure that the messages were logged properly
        $this->assertEquals(6, count(explode("\n", trim(stream_get_contents($es)))));
        // Make sure the entire stream was read
        $this->assertEquals('', stream_get_contents($is));
    }
}
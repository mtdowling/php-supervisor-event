<?php

namespace Supervisor;

/**
 * Handles communication between Supervisord events and an event callback
 *
 * @author Michael Dowling <mtdowling@gmail.com>
 */
class EventListener
{
    const ACKNOWLEDGED = 'ACKNOWLEDGED';
    const READY = 'READY';
    const BUSY = 'BUSY';
    const QUIT = 'quit';

    /**
     * @var resource Input stream used to retrieve text
     */
    protected $inputStream;

    /**
     * @var resource Output stream used to send text
     */
    protected $outputStream;

    /**
     * @var resource Error stream used to write log messages and errors
     */
    protected $errorStream;

    /**
     * Create a new EventListener
     */
    public function __construct()
    {
        $this->inputStream = STDIN;
        $this->outputStream = STDOUT;
        $this->errorStream = STDERR;
    }

    /**
     * Set the error stream
     *
     * @param resource $stream Stream to send logs and errors
     *
     * @return EventListener
     */
    public function setErrorStream($stream)
    {
        $this->errorStream = $stream;

        return $this;
    }

    /**
     * Set the input stream
     *
     * @param resource $stream Stream to retrieve input from
     *
     * @return EventListener
     */
    public function setInputStream($stream)
    {
        $this->inputStream = $stream;

        return $this;
    }

    /**
     * Set the output stream
     *
     * @param resource $stream Stream to send output to
     *
     * @return EventListener
     */
    public function setOutputStream($stream)
    {
        $this->outputStream = $stream;

        return $this;
    }

    /**
     * Poll stdin for Supervisord notifications and dispatch notifications to
     * the callback function which should accept this object (EventListener) as
     * its first parameter and an EventNotification as its second.  The callback
     * should return TRUE if it was successful, FALSE on failure, or 'quit' to
     * break from the event listener loop.
     *
     * @param Closure|array Closure callback
     */
    public function listen($callback)
    {
        $this->sendReady();

        while (true) {
            if (!$input = trim($this->readLine())) {
                continue;
            }
            $headers = EventNotification::parseData($input);
            $payload = fread($this->inputStream, (int) $headers['len']);
            $notification = new EventNotification($input, $payload, $headers);
            $result = call_user_func($callback, $this, $notification);
            if (true === $result) {
                $this->sendComplete();
            } else if (false === $result) {
                $this->sendFail();
            } else if ($result == 'quit') {
                break;
            }
            $this->sendReady();
        }
    }

    /**
     * Log data to STDERR
     *
     * @param string $message Message to log to STDERR
     */
    public function log($message)
    {
        fwrite($this->errorStream, '[Supervisord Event] ' . date('Y-m-d H:i:s') . ': ' . $message . "\n");
    }

    /**
     * Read a line from the input stream
     *
     * @return string
     */
    public function readLine()
    {
        return fgets($this->inputStream);
    }

    /**
     * Send an ACKNOWLEDGED state to Supervisord
     */
    public function sendAcknowledged()
    {
        fwrite($this->outputStream, self::ACKNOWLEDGED . "\n");
    }

    /**
     * Send a BUSY state to Supervisord
     */
    public function sendBusy()
    {
        fwrite($this->outputStream, self::BUSY . "\n");
    }

    /**
     * Send a completion result
     */
    public function sendComplete()
    {
        fwrite($this->outputStream, "RESULT 2\nOK");
    }

    /**
     * Send a fail result
     */
    public function sendFail()
    {
        fwrite($this->outputStream, "RESULT 4\nFAIL");
    }

    /**
     * Send a READY state to Supervisord
     */
    public function sendReady()
    {
        fwrite($this->outputStream, self::READY . "\n");
    }
}
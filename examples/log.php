<?php
/**
 * Here is a simple example of creating a supervisor event listener
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'php-supervisor-event.phar';

use Supervisor\EventListener;
use Supervisor\EventNotification;

$listener = new Supervisor\EventListener();
$listener->listen(function(EventListener $listener, EventNotification $event) {
    // Log information about the event
    $listener->log($event->getEventName());
    $listener->log($event->getServer());
    $listener->log($event->getPool());
    // Try messing around with supervisorctl to restart processes and see what
    // data is available
    $listener->log(var_export($event->getData(), true));
    return true;
});
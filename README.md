PHP Supervisor Event Framework
==============================

Receives event notifications from Supervisor and sends the parsed notification
to a callback function.

Requirements
============

PHP 5.3
Supervisord

Running the example
===================

Open your supervisord.conf file and add the following:

    [eventlistener:event_listener]
    command=php /path/to/examples/log.php
    process_name=%(program_name)s_%(process_num)02d
    numprocs=1
    events=PROCESS_STATE_STARTING,TICK_5
    autostart=true
    autorestart=unexpected
    stderr_logfile_backups=3

Replace /path/to with the correct path.  More event listener options can be
found at http://supervisord.org/events.html

Now run:

    supervisorctl reload

Example event script
--------------------

    <?php

    // include the phar file
    require_once '/path/to/build/php-supervisor-event.phar';

    use Supervisor\EventListener;
    use Supervisor\EventNotification;

    $listener = new Supervisor\EventListener();
    $listener->listen(function(EventListener $listener, EventNotification $event) {
        $listener->log($event->getEventName());
        $listener->log($event->getServer());
        $listener->log($event->getPool());
        // Try messing around with supervisorctl to restart processes and see what
        // data is available
        $listener->log(var_export($event->getData(), true));
        return true;
    });
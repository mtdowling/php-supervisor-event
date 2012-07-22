PHP Supervisor Event Framework
==============================

Receives event notifications from Supervisor and sends the parsed notification
to a callback function.

Requirements
============

* PHP 5.3
* Supervisord

Installation
============

The recommended installation method is through [Composer](http://getcomposer.org).

1. Add ``mtdowling/supervisor-event`` as a dependency in your project's ``composer.json`` file:

        {
            "require": {
                "mtdowling/supervisor-event": "*"
            }
        }

2. Download and install Composer:

        curl -s http://getcomposer.org/installer | php

3. Install your dependencies:

        php composer.phar install

4. Require Composer's autoloader

    Composer also prepares an autoload file that's capable of autoloading all of the classes in any of the libraries that it downloads. To use it, just add the following line to your code's bootstrap process:

        require 'vendor/autoload.php';

You can find out more on how to install Composer, configure autoloading, and other best-practices for defining dependencies at [getcomposer.org](http://getcomposer.org).

Example event script
--------------------

    <?php

    // include the composer autoloader
    require_once __DIR__ . '/vendor/autoload.php';

    use Mtdowling\Supervisor\EventListener;
    use Mtdowling\Supervisor\EventNotification;

    $listener = new EventListener();
    $listener->listen(function(EventListener $listener, EventNotification $event) {
        $listener->log($event->getEventName());
        $listener->log($event->getServer());
        $listener->log($event->getPool());
        // Try messing around with supervisorctl to restart processes and see what
        // data is available
        $listener->log(var_export($event->getData(), true));
        return true;
    });

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

Replace /path/to with the correct path.  More event listener options can be
found at http://supervisord.org/events.html

Now run:

    supervisorctl reload

<?php
/**
 * Appends process state changes to a file that can later be consumed by
 * something else periodically (e.g. collectd)
 *
 * Add the following to your supervisord.conf:
 *
 * [eventlistener:event_listener]
 * command=php /path/to/examples/processes.php /tmp/processes.txt
 * process_name=%(program_name)s_%(process_num)02d
 * events=PROCESS_STATE_STARTING,PROCESS_STATE_STOPPING,PROCESS_STATE_STOPPED,PROCESS_STATE_FATAL,PROCESS_STATE_UNKNOWN,PROCESS_STATE_EXITED,PROCESS_STATE_BACKOFF,PROCESS_STATE_RUNNING
 * numprocs=1
 * autostart=true
 * autorestart=unexpected
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Mtdowling\Supervisor\EventListener;
use Mtdowling\Supervisor\EventNotification;

// Requires a file name as an argument
if (!isset($argv[1])) {
    echo 'This script requires that a file name be passed as a single parameter';
    exit(1);
}

$filename = $argv[1];
$f = fopen($filename, 'a');
if (!$f) {
    echo 'Unable to open ' . $filename . ' for writing';
    exit(1);
}

$listener = new EventListener();
$listener->listen(function(EventListener $listener, EventNotification $event) use ($f) {
    $state = $event->getEventName();
    if (strpos($state, 'PROCESS_STATE_') !== false) {
        $name = $event->getData('groupname')
            ? $event->getData('groupname')
            : $event->getData('processname');
        fwrite($f, "{$name},{$state}\n");
    }
    return true;
});

fclose($f);

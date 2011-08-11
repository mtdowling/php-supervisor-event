<?php

namespace Supervisor\Tests;

error_reporting(E_ALL | E_STRICT);

require_once 'PHPUnit/TextUI/TestRunner.php';

$prefix =  __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    . 'src' . DIRECTORY_SEPARATOR . 'Supervisor' . DIRECTORY_SEPARATOR;
require_once $prefix . 'EventNotification.php';
require_once $prefix . 'EventListener.php';
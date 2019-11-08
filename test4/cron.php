<?php
if (!function_exists('pcntl_signal')) {
    printf("Error, you need to enable the pcntl extension in your php binary, see http://www.php.net/manual/en/pcntl.installation.php for more info%s", PHP_EOL);
    exit(1);
}

error_reporting(E_ALL);

/* Allow the script to hang around waiting for connections. */
set_time_limit(0);

use Services\Daemon;
use Services\SchedulerManager;

require_once __DIR__ . '/bootstrap.php';

$daemon = new Daemon(new SchedulerManager(), '0.0.0.0', '12345', true);
try {
    $daemon->run();
} catch (\Throwable $e) {
    if (!$daemon->isLock()) {
        $daemon->unlock();
    }
    echo 'Got an error with message: ' . $e->getMessage() . "\n";
    echo 'file://' . $e->getFile() . ':' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
echo 'Done successful';

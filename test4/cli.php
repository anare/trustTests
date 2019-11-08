<?php

use Services\DaemonClient;

require_once __DIR__ . '/bootstrap.php';

echo 'PHP Cli Scheduler by Anar Alishov v1.0'."\n";

if ($argc < 2) {
    echo "Usage:\n\t ${argv[0]} <datetime> <type://task>\n";
    exit(1);
}

$client = new DaemonClient();
if (!$client->connect()) {
    echo 'Could not connect to server' . "\n";
    exit(2);
}

//Example: '+5 seconds' php://task
//Example: +7 seconds shell://task
$taskAddResponse = $client->send('task ' . $argv[1] . ',' . $argv[2]);
if ($taskAddResponse === 'added') {
    echo 'Task successful added' . "\n";
} else {
    echo 'Error on task adding' . "\n";
}
$response = $client->send('quit');
if ($response === 'quit') {
    echo 'Successful quit'. "\n";
}

$client->close();
echo 'Connection closed'. "\n";

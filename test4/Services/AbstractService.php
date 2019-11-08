<?php

namespace Services;

class AbstractService
{
    public const DAEMON_INTERVAL = 1;

    /**
     * @var Properties
     */
    protected $properties;

    public function __construct($debug = false, $logFile = 'php://stdout', ?Properties $properties = null)
    {
        $this->stdIn             = fopen('php://stdin', 'r');
        stream_set_blocking($this->stdIn, false);
        $this->stdOut            = fopen($logFile, 'w');
        $this->properties        = $properties ?? new Properties();
        $this->properties->debug = $debug;
    }

    protected function debug($msg, $date = false)
    {
        if (isset($this->properties->debug) && $this->properties->debug) {
            $dateTimestamp = $date ? date(DATE_ATOM) . ': ' : '';
            fwrite($this->stdOut, $dateTimestamp);
            $message = (is_string($msg) && strlen($msg) === 1) ? $msg : (!is_string($msg) ? json_encode($msg) : $msg) . "\n";
            fwrite($this->stdOut, $message);
        }
    }
}

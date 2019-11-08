<?php

namespace Services;

/**
 * Class Daemon
 *
 * @package Services
 */
class DaemonClient extends AbstractService
{
    /**
     * Interval of checking tasks
     */
    public const DAEMON_INTERVAL = 1;
    /**
     *
     */
    const        TMP_INPUT = '/tmp/input';

    /**
     * @var Properties
     */
    protected $properties;

    /**
     * @var SchedulerManager
     */
    protected $scheduleManager;

    /**
     * @var array
     */
    protected $running = [];

    /**
     * @var string
     */
    protected $lockFile;

    /**
     * @var bool
     */
    protected $connected = false;

    /**
     * @var string
     */
    private $address;

    /**
     * @var int
     */
    private $port;

    /**
     * @var
     */
    private $socket;

    /**
     * Daemon constructor.
     *
     * @param SchedulerManager $scheduleManager
     * @param string           $address
     * @param int              $port
     * @param bool             $debug
     * @param string           $lockFile
     * @param string           $logFile
     */
    public function __construct(
        $address = '127.0.0.1',
        $port = 12345,
        $debug = false,
        $lockFile = '/tmp/phpcron.lock',
        $logFile = 'php://stdout'
    ) {
        parent::__construct($debug, $logFile);
        $this->lockFile = $lockFile;
        $this->address  = $address;
        $this->port     = $port;
    }

    /**
     *
     */
    public function connect()
    {
        $this->socket = fsockopen($this->address, $this->port, $errno, $errstr, 30);
        if (!$this->socket) {
            throw new \Error("$errstr ($errno)\n");
        }
        $ready = trim(fgets($this->socket, 128));

        if ($ready === 'ready') {
            $this->connected = true;
        }

        return $this->connected;
    }

    public function send($command) {
        $command = $command . "\n";
        $this->debug('Send command: ' . $command);
        fwrite($this->socket, $command);
        $response = trim(fgets($this->socket, 512));

        return $response;
    }

    public function close()
    {
        fclose($this->socket);
    }
}

<?php
declare(ticks=1);

namespace Services;

/**
 * Class Daemon
 *
 * @package Services
 */
class Daemon extends AbstractService
{
    /**
     * Interval of checking tasks
     */
    public const DAEMON_INTERVAL = 1;
    const        TMP_INPUT       = '/tmp/input';

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
    protected $alive = true;
    protected $iamServer = true;
    /**
     * @var string
     */
    private $address;
    /**
     * @var int
     */
    private $port;
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
        SchedulerManager $scheduleManager,
        $address = '127.0.0.1',
        $port = 12345,
        $debug = false,
        $lockFile = '/tmp/phpcron.lock',
        $logFile = 'php://stdout'
    ) {
        parent::__construct($debug, $logFile);
        $this->scheduleManager = $scheduleManager;
        $this->lockFile        = $lockFile;
        $this->address         = $address;
        $this->port            = $port;
    }

    public function isLock()
    {
        if (file_exists($this->lockFile)) {
            $runningPid = trim(file_get_contents($this->lockFile));
            if ($runningPid !== posix_getpid()) {
                return $runningPid;
            }
        }

        return false;
    }

    public function lock()
    {
        $lockPid = $this->isLock();
        if ($lockPid === false) {
            $lockPid = posix_getpid();
            $this->debug(['Current running PID: ' => $lockPid]);
            file_put_contents($this->lockFile, $lockPid);
            if (!@mkdir($concurrentDirectory = self::TMP_INPUT . $lockPid, 0777) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }

            pcntl_signal(SIGTERM, [$this, 'bySignalShutdown']);
            pcntl_signal(SIGINT, [$this, 'bySignalShutdown']);
            pcntl_signal(SIGQUIT, [$this, 'bySignalShutdown']);
            pcntl_signal(SIGHUP, [$this, 'bySignalShutdown']);
            pcntl_signal(SIGUSR1, [$this, 'bySignalShutdown']);
        }

        return $lockPid;
    }

    public function bySignalShutdown($signal, $signinfo)
    {
        $this->debug("Got signal $signal: Exiting from PHP Cron Service");
        $this->unlock();
        $this->alive = false;
    }

    public function unlock($signal = null, $signinfo = null)
    {
        if (!$this->iamServer) {
            return false;
        }
        if ($this->isLock() !== false) {
            unlink($this->lockFile);
            $this->debug('Service is unlocked');
            if ($this->socket) {
                @socket_close($this->socket);
            }

            return true;
        }

        return false;
    }

    /**
     * @param int $interval
     *
     * @throws \Exception
     */
    public function run($interval = self::DAEMON_INTERVAL)
    {
        if ($this->isLock() !== false) {
            throw new \Error('PHP Cron already running');
        }
        $this->lock();
        $this->debug("\n", true);
        $this->socketSetup();
        while ($this->alive) {
            $this->readInput();
            $this->process(new \DateTime());
            $this->checkTasks();
            $this->debug('.');
            sleep($interval);
        }
    }

    public function socketSetup()
    {
        if (($socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
            throw new \Error("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
        }

        $this->socket = $socket;
        if (@socket_bind($this->socket, $this->address, $this->port) === false) {
            throw new \Error("socket_bind() failed: reason: " . socket_strerror(socket_last_error($this->socket)));
        }

        if (socket_listen($this->socket, 5) === false) {
            throw new \Error("socket_listen() failed: reason: " . socket_strerror(socket_last_error($this->socket)));
        }

        socket_set_nonblock($this->socket);
    }

    public function readInput()
    {
        if (($clientSocket = socket_accept($this->socket)) !== false) {
            $this->debug("Client $clientSocket has connected\n");
            $this->handleTcpRequest($clientSocket);
        }
    }

    public function addTask($body)
    {
        [$datatime, $task] = explode(',', $body, 2);
        $this->debug(['datatime' => $datatime, 'task' => $task]);
        try {
            $dateTime = new \DateTime($datatime);
            $this->debug(['now' => (new \DateTime())->format(DATE_ATOM), 'datatime' => $dateTime->format(DATE_ATOM)]);
        } catch (\Exception $e) {
            return false;
        }
        try {
            $event = EventFactory::factory($dateTime, $task);
            return $this->scheduleManager->register($event);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param \DateTime $now
     *
     * @return $this
     * @throws \ErrorException
     */
    public function process(\DateTime $now)
    {
        $tasks = $this->scheduleManager->tasksToExecute($now);
        $this->execute($tasks);

        return $this;
    }

    /**
     * @param \DateTime $now
     *
     * @return $this
     * @throws \ErrorException
     */
    public function checkTasks()
    {
        $pids = array_keys($this->running);
        foreach ($pids as $pid) {
            $result = pcntl_waitpid($pid, $status, WNOHANG);
            if ($result < 1) {
                $hash = $this->running[$pid];
                $this->scheduleManager->taskComplete($hash);
                unset($this->running[$pid]);
            }
        }

        return $this;
    }

    /**
     * @param EventInterface[] $tasks
     *
     * @throws \ErrorException
     */
    public function execute($tasks)
    {
        foreach ($tasks as $hash => $event) {
            $this->executeOneTask($hash, $event);
        }
    }

    /**
     * @param EventInterface $event
     *
     * @throws \ErrorException
     */
    public function executeOneTask($hash, $event)
    {
        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new \ErrorException('Can\'t create process');
        }
        if ($pid !== 0) {
            $this->running[$pid] = $hash;
            $this->scheduleManager->executedTask($hash);
        } else {
            $event->execute();
            $this->debug('I am Child Exited');
            exit(0);
        }
    }

    protected function handleTcpRequest($clientSocket)
    {
        $alive = true;
        socket_set_block($clientSocket);
        socket_write($clientSocket, 'ready'."\n");
        while ($alive) {
            $query = socket_read($clientSocket, 1024);
            $body  = '';
            $query = strtolower(trim($query));
            if (strpos($query, ' ') !== false) {
                [$query, $body] = explode(' ', $query, 2);
            }
            switch ($query) {
                case 'task':
                    $result = $this->addTask($body);
                    $done = @socket_write($clientSocket, ($result ? 'added' : 'error')."\n");
                    break;
                case 'quit':
                    $done = @socket_write($clientSocket, 'quit'."\n");
                    $alive = false;
                    break;
            }
            if ($done === false) {
                $alive = false;
            }
        }
        @socket_close($clientSocket);
    }
}

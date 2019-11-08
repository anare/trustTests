<?php

namespace Services;

class SchedulerManager extends AbstractService
{
    /**
     * @var Properties
     */
    protected $properties;

    /**
     * @var EventInterface[]
     */
    protected $awaiting = [];

    /**
     * @var EventInterface[]
     */
    protected $progress = [];

    /**
     * @var EventInterface[]
     */
    protected $complete = [];

    public function __construct($debug = false, $logFile = 'php://stdout')
    {
        parent::__construct($debug, $logFile);
    }

    public function register(EventInterface $event)
    {
        $this->awaiting[$event->getHash()] = $event;

        return $event->getHash();
    }

    public function unregister(EventInterface $event)
    {
        $hash = $event->getHash();
        if (isset($this->awaiting[$hash])) {
            unset($this->awaiting[$hash]);
        }
        if (isset($this->progress[$hash])) {
            unset($this->progress[$hash]);
        }
        if (isset($this->complete[$hash])) {
            unset($this->complete[$hash]);
        }
    }

    public function tasks()
    {
        return $this->awaiting;
    }

    public function getTask($hash)
    {
        if (isset($this->awaiting[$hash])) {
            return $this->awaiting[$hash];
        }
        if (isset($this->progress[$hash])) {
            return $this->progress[$hash];
        }
        if (isset($this->complete[$hash])) {
            return $this->complete[$hash];
        }

        return null;
    }

    public function tasksToExecute(\DateTime $now)
    {
        /** @var EventInterface[] $result */
        $result = [];
        foreach ($this->awaiting as $hash => $event) {
            if ($event->isScheduled($now)) {
                $result[$hash] = $event;
            }
        }

        return $result;
    }

    public function executedTask($hash)
    {
        $this->debug(['execute' => ['task' => $hash]]);
        if (!isset($this->awaiting[$hash])) {
            $this->debug('This task is not in awaiting list');
            return null;
        }
        $event = $this->awaiting[$hash];
        $event->inProgress();
        unset($this->awaiting[$hash]);
        $this->progress[$event->getHash()] = $event;

        return $event;
    }

    public function taskComplete($hash)
    {
        $this->debug(['complete' => ['task' => $hash]]);
        if (!isset($this->progress[$hash])) {
            $this->debug('This task is not in progress');
            return null;
        }
        $event = $this->progress[$hash];
        $event->complete();
        unset($this->progress[$hash]);
        $this->complete[$event->getHash()] = $event;

        return $event;
    }
}

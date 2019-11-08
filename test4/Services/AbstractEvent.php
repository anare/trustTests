<?php

namespace Services;

abstract class AbstractEvent implements EventInterface
{
    public const STATUS_AWAITING    = 'awaiting';
    public const STATUS_IN_PROGRESS = 'progress';
    public const STATUS_COMPLETE    = 'complete';

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var \DateTime
     */
    protected $dateTime;

    /**
     * @var string
     */
    protected $task;

    /**
     * @var string
     */
    protected $status;

    public function __construct(\DateTime $dateTime, string $task)
    {
        $this->dateTime = $dateTime;
        $this->task     = $task;
        $this->hash     = md5($dateTime->getTimestamp() . $task);
        $this->status   = self::STATUS_AWAITING;
    }

    public function isScheduled(\DateTime $now): bool
    {
        return ($this->dateTime <= $now);
    }

    public function getTask()
    {
        return $this->task;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return EventInterface
     */
    public function setStatus(string $status): EventInterface
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @param string $status
     *
     * @return EventInterface
     */
    public function inProgress(): EventInterface
    {
        $this->status = self::STATUS_IN_PROGRESS;

        return $this;
    }

    /**
     * @param string $status
     *
     * @return EventInterface
     */
    public function complete(): EventInterface
    {
        $this->status = self::STATUS_COMPLETE;

        return $this;
    }
}

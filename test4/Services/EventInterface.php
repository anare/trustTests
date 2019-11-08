<?php

namespace Services;

interface EventInterface
{
    public function isScheduled(\DateTime $now): bool;

    public function getTask();

    public function getHash(): string;

    public function getStatus(): string;

    public function setStatus(string $status): EventInterface;

    public function inProgress(): EventInterface;

    public function complete(): EventInterface;

    public function execute();
}

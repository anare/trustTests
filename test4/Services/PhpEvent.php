<?php

namespace Services;

class PhpEvent extends AbstractEvent
{
    public function execute()
    {
        $task = ucfirst($this->task);
        switch ($this->task) {
            case 'task':
                require __DIR__ . '/../Tasks/Php' . $task . '.php';
            default:
                break;
        }
    }
}

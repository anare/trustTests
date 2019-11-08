<?php

namespace Services;

class ShellEvent extends AbstractEvent {

    public function execute()
    {
        $task = ucfirst($this->task);
        switch ($this->task) {
            case 'task':
                exec(__DIR__ . '/../Tasks/Shell' . $task . '.sh', $output);
                print_r($output);
            default:
                break;
        }
    }
}

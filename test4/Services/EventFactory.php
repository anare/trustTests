<?php

namespace Services;

class EventFactory
{
    const DELIMITER = '://';

    public static function factory(\DateTime $dateTime, string $task): EventInterface
    {
        if (strpos($task, self::DELIMITER) === false) {
            throw new \RuntimeException(sprintf('Wrong Event task provided, with %s', $task));
        }
        [$type, $task] = explode(self::DELIMITER, $task);
        $type = strtolower(trim($type));
        $task = trim($task);
        switch ($type) {
            case 'php':
                return new PhpEvent($dateTime, $task);
            case 'shell':
                return new ShellEvent($dateTime, $task);
            default:
                break;
        }

        throw new \RuntimeException('Unregistered task type');
    }
}

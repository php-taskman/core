<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Event;

use Symfony\Component\EventDispatcher\Event;

class TaskFactory extends Event
{
    private $factory;
    /**
     * @var array
     */
    private $task;

    /**
     * TaskFactory constructor.
     *
     * @param array $task
     */
    public function __construct(array $task)
    {
        $this->task = $task;
    }

    public function getFactory()
    {
        return $this->factory;
    }

    public function getTask()
    {
        return $this->task;
    }

    public function getTaskArguments()
    {
        $clone = $this->task;
        unset($clone['task']);

        $arguments = $this->getFactory()::ARGUMENTS;

        $args = [];
        foreach ($arguments as $argument) {
            if (isset($this->task[$argument])) {
                $args[$argument] = $this->task[$argument];
            }
        }

        return $args;
    }

    public function getTaskName()
    {
        return $this->task['task'];
    }

    public function setFactory($factory)
    {
        $this->factory = $factory;
    }
}

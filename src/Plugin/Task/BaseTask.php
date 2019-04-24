<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

use PhpTaskman\Core\Contract\TaskInterface;
use Robo\Contract\BuilderAwareInterface;
use Robo\TaskAccessor;

abstract class BaseTask extends \Robo\Task\BaseTask implements TaskInterface, BuilderAwareInterface
{
    use TaskAccessor;
    public const ARGUMENTS = [];

    public const NAME = 'NULL';

    private $options;

    /**
     * @var array
     */
    private $task;

    public function getOptions()
    {
        return $this->options;
    }

    public function getTask()
    {
        return $this->task;
    }

    public function getTaskArguments()
    {
        $task = $this->getTask();
        unset($task['task']);

        $arguments = \array_combine(
            static::ARGUMENTS,
            static::ARGUMENTS
        );

        if (empty($arguments)) {
            return $task;
        }

        foreach ($task as $key => $value) {
            if (isset($arguments[$key])) {
                continue;
            }

            unset($task[$key]);
        }

        return $task;
    }

    public function setOptions(array $options = [])
    {
        $this->options = $options;
    }

    public function setTask(array $task = [])
    {
        $this->task = $task;
    }
}

<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin;

use PhpTaskman\Core\Contract\TaskInterface;
use Robo\Contract\BuilderAwareInterface;
use Robo\TaskAccessor;

abstract class BaseTask extends \Robo\Task\BaseTask implements TaskInterface, BuilderAwareInterface
{
    use TaskAccessor;

    public const ARGUMENTS = [];
    public const NAME = 'NULL';

    /**
     * @var array
     */
    private $options;

    /**
     * @var array
     */
    private $task;

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return array
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @return array
     */
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

    /**
     * @param array $options
     */
    public function setOptions(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @param array $task
     */
    public function setTask(array $task = [])
    {
        $this->task = $task;
    }
}

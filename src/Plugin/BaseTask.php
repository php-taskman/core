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
    private $arguments;

    /**
     * @return array
     */
    public function getTaskArguments()
    {
        $arguments = $this->arguments;

        unset($arguments['task']);

        $argumentsAllowed = \array_combine(
            static::ARGUMENTS,
            static::ARGUMENTS
        );

        if (empty($argumentsAllowed)) {
            return $arguments;
        }

        foreach ($arguments as $key => $value) {
            if (isset($argumentsAllowed[$key])) {
                continue;
            }

            unset($arguments[$key]);
        }

        return $arguments;
    }

    /**
     * @param array $arguments
     */
    public function setTaskArguments(array $arguments = [])
    {
        $this->arguments = $arguments;
    }
}

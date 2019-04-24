<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin;

use PhpTaskman\Core\Contract\TaskInterface;
use Robo\Common\BuilderAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\TaskAccessor;

abstract class BaseTask extends \Robo\Task\BaseTask implements TaskInterface, BuilderAwareInterface
{
    use BuilderAwareTrait;
    use TaskAccessor;

    public const ARGUMENTS = [];
    public const NAME = 'NULL';

    /**
     * @var array
     */
    private $arguments;

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function setTaskArguments(array $arguments = [])
    {
        $this->arguments = $arguments;

        return $this;
    }
}

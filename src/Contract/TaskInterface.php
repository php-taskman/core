<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Contract;

interface TaskInterface extends \Robo\Contract\TaskInterface
{
    public function getOptions();

    public function getTask();

    public function getTaskArguments();

    public function setOptions(array $options = []);

    public function setTask(array $task = []);
}

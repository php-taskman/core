<?php

namespace PhpTaskman\Core\Contract;

interface TaskInterface extends \Robo\Contract\TaskInterface
{
    public function getTaskArguments();

    public function setTaskArguments(array $arguments = []);
}

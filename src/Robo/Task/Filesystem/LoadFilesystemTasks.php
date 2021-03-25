<?php

declare(strict_types=1);

namespace PhpTaskman\Core\Robo\Task\Filesystem;

trait LoadFilesystemTasks
{
    /**
     * @param mixed $task
     * @param mixed $options
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function taskFilesystemFactory($task, $options)
    {
        return $this->task(Filesystem::class)->{$task}(...array_values($options));
    }
}

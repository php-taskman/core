<?php

namespace PhpTaskman\Core\Robo\Task\Filesystem;

/**
 * Trait LoadFilesystemTasks.
 */
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

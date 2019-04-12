<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Robo\Task\ProcessConfigFile;

/**
 * Trait LoadProcessConfigFileTasks.
 */
trait LoadProcessConfigFileTasks
{
    /**
     * @param mixed $source
     * @param mixed $destination
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function taskProcessConfigFile($source, $destination)
    {
        return $this->task(ProcessConfigFile::class, $source, $destination);
    }
}

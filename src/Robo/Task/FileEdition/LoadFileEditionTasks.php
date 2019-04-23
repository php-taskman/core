<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Robo\Task\FileEdition;

/**
 * Class LoadFileEditionTasks.
 */
trait LoadFileEditionTasks
{
    /**
     * @param string $filename
     *
     * @return mixed
     */
    protected function taskWritePrependToFile($filename)
    {
        return $this->task(Prepend::class, $filename);
    }
}

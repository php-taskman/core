<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Robo\Task\Filesystem;

use Robo\Task\Filesystem\FilesystemStack;
use Robo\Task\Filesystem\loadTasks;
use Robo\TaskAccessor;

/**
 * Class Filesystem.
 */
final class Filesystem extends FilesystemStack
{
    use loadTasks;
    use TaskAccessor;

    // phpcs:disable
    /**
     * {@inheritdoc}
     */
    protected function _copy($from, $to, $force = false)
    {
        if (\is_dir($from)) {
            return $this->taskCopyDir([$from => $to])->run();
        }

        parent::_copy($from, $to, $force);
    }
    // phpcs:enable
}

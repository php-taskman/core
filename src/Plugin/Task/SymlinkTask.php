<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

use PhpTaskman\Core\Plugin\FilesystemTask;

final class SymlinkTask extends FilesystemTask
{
    public const ARGUMENTS = [
        'from',
        'to',
        'copyOnWindows',
    ];
    public const NAME = 'symlink';
}

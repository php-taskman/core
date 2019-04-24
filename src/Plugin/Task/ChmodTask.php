<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

use PhpTaskman\Core\Plugin\FilesystemTask;

final class ChmodTask extends FilesystemTask
{
    public const ARGUMENTS = [
        'file',
        'permissions',
        'umask',
        'recursive',
    ];
    public const NAME = 'chmod';
}

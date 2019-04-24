<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

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

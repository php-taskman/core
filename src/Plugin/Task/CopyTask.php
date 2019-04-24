<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

use PhpTaskman\Core\Plugin\FilesystemTask;

final class CopyTask extends FilesystemTask
{
    public const ARGUMENTS = [
        'from',
        'to',
        'force',
    ];
    public const NAME = 'copy';
}

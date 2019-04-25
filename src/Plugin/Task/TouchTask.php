<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

use PhpTaskman\Core\Plugin\FilesystemTask;

final class TouchTask extends FilesystemTask
{
    public const ARGUMENTS = [
        'file',
        'time',
        'atime',
    ];
    public const NAME = 'touch';
}

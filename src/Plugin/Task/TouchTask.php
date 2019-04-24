<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

final class TouchTask extends FilesystemTask
{
    public const ARGUMENTS = [
        'file',
        'time',
        'atime',
    ];
    public const NAME = 'touch';
}

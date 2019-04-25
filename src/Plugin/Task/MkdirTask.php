<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

use PhpTaskman\Core\Plugin\FilesystemTask;

final class MkdirTask extends FilesystemTask
{
    public const ARGUMENTS = [
        'dir',
        'mode',
    ];
    public const NAME = 'mkdir';
}

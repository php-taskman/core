<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

use PhpTaskman\Core\Plugin\FilesystemTask;

final class ChownTask extends FilesystemTask
{
    public const ARGUMENTS = [
        'file',
        'user',
        'recursive',
    ];
    public const NAME = 'chown';
}

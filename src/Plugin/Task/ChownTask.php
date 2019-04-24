<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

final class ChownTask extends FilesystemTask
{
    public const ARGUMENTS = [
        'file',
        'user',
        'recursive',
    ];
    public const NAME = 'chown';
}

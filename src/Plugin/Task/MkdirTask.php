<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

final class MkdirTask extends FilesystemTask
{
    public const ARGUMENTS = [
        'dir',
        'mode',
    ];
    public const NAME = 'mkdir';
}

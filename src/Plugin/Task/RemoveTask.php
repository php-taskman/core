<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

final class RemoveTask extends FilesystemTask
{
    public const ARGUMENTS = [
        'file',
    ];
    public const NAME = 'remove';
}

<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

final class MirrorTask extends FilesystemTask
{
    public const ARGUMENTS = [
        'from',
        'to',
    ];
    public const NAME = 'mirror';
}

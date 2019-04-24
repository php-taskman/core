<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

final class ChgrpTask extends FilesystemTask
{
    public const ARGUMENTS = [
        'file',
        'group',
        'recursive',
    ];
    public const NAME = 'chgrp';
}

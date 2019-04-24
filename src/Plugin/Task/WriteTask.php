<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

use PhpTaskman\Core\Robo\Task\ProcessConfigFile\LoadProcessConfigFileTasks;
use Robo\Common\BuilderAwareTrait;
use Robo\Task\File\loadTasks;

final class WriteTask extends BaseTask
{
    use BuilderAwareTrait;
    use BuilderAwareTrait;
    use LoadProcessConfigFileTasks;
    use loadTasks;

    public const ARGUMENTS = [
        'file',
        'text',
    ];

    public const NAME = 'write';

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $arguments = $this->getTask();

        return $this->collectionBuilder()->addTaskList([
            $this->taskWriteToFile($arguments['file'])->text($arguments['text']),
            $this->taskProcessConfigFile($arguments['file'], $arguments['file']),
        ])->run();
    }
}

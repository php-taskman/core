<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

use PhpTaskman\Core\Robo\Task\ProcessConfigFile\LoadProcessConfigFileTasks;
use Robo\Task\File\loadTasks;

final class AppendTask extends BaseTask
{
    use LoadProcessConfigFileTasks;
    use loadTasks;

    public const ARGUMENTS = [
        'file',
        'text',
    ];

    public const NAME = 'append';

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $arguments = $this->getTaskArguments();

        return $this->collectionBuilder()->addTaskList([
            $this->taskWriteToFile($arguments['file'])->append()->text($arguments['text']),
            $this->taskProcessConfigFile($arguments['file'], $arguments['file']),
        ])->run();
    }
}

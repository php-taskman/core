<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

use PhpTaskman\Core\Plugin\BaseTask;
use PhpTaskman\Core\Robo\Task\ProcessConfigFile\LoadProcessConfigFileTasks;
use Robo\Common\BuilderAwareTrait;
use Robo\Task\File\loadTasks;

final class ProcessTask extends BaseTask
{
    use BuilderAwareTrait;
    use BuilderAwareTrait;
    use LoadProcessConfigFileTasks;
    use loadTasks;

    public const ARGUMENTS = [
        'source',
        'destination',
    ];
    public const NAME = 'process';

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $arguments = $this->getTaskArguments();

        return $this
            ->collectionBuilder()
            ->addTaskList([
                $this->taskProcessConfigFile($arguments['source'], $arguments['destination']),
            ])
            ->run();
    }
}

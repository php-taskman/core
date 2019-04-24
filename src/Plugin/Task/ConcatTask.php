<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

use PhpTaskman\Core\Robo\Task\ProcessConfigFile\LoadProcessConfigFileTasks;
use Robo\Common\BuilderAwareTrait;
use Robo\Task\File\loadTasks;

final class ConcatTask extends BaseTask
{
    use BuilderAwareTrait;
    use BuilderAwareTrait;
    use LoadProcessConfigFileTasks;
    use loadTasks;

    public const ARGUMENTS = [
        'files',
        'to',
    ];

    public const NAME = 'concat';

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $arguments = $this->getTask();

        return $this
            ->collectionBuilder()
            ->addTaskList([
                $this->taskConcat($arguments['files'])->to($arguments['to']),
                $this->taskProcessConfigFile($arguments['to'], $arguments['to']),
            ])
            ->run();
    }
}

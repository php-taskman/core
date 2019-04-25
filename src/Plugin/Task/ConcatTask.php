<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

use PhpTaskman\Core\Plugin\BaseTask;
use Robo\Common\BuilderAwareTrait;
use Robo\Task\File\loadTasks;

final class ConcatTask extends BaseTask
{
    use BuilderAwareTrait;
    use BuilderAwareTrait;
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
        $arguments = $this->getTaskArguments();

        /** @var \PhpTaskman\Core\Plugin\Task\ProcessTask $processTask */
        $processTask = $this->task(ProcessTask::class);
        $processTask->setTaskArguments([
            'from' => $arguments['to'],
            'to' => $arguments['to'],
        ]);

        return $this
            ->collectionBuilder()
            ->addTaskList([
                $this->taskConcat($arguments['files'])->to($arguments['to']),
                $processTask,
            ])
            ->run();
    }
}

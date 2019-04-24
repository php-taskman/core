<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

use PhpTaskman\Core\Plugin\BaseTask;
use Robo\Common\BuilderAwareTrait;
use Robo\Task\File\loadTasks;

final class WriteTask extends BaseTask
{
    use BuilderAwareTrait;
    use BuilderAwareTrait;
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

        /** @var \PhpTaskman\Core\Plugin\Task\ProcessTask $processTask */
        $processTask = $this->task(ProcessTask::class);
        $processTask->setTask([
            'from' => $arguments['file'],
            'to' => $arguments['file'],
        ]);

        return $this->collectionBuilder()->addTaskList([
            $this->taskWriteToFile($arguments['file'])->text($arguments['text']),
            $processTask,
        ])->run();
    }
}

<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

use PhpTaskman\Core\Plugin\BaseTask;
use Robo\Common\BuilderAwareTrait;
use Robo\Task\File\Write;

final class PrependTask extends BaseTask
{
    use BuilderAwareTrait;

    public const ARGUMENTS = [
        'file',
        'text',
    ];
    public const NAME = 'prepend';

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $arguments = $this->getTaskArguments();

        /** @var \PhpTaskman\Core\Plugin\Task\ProcessTask $processTask */
        $processTask = $this->task(ProcessTask::class);
        $processTask->setTaskArguments([
            'from' => $arguments['file'],
            'to' => $arguments['file'],
        ]);

        /** @var \Robo\Task\File\Write $writeTask */
        $writeTask = $this->task(Write::class, $arguments['file']);

        return $this->collectionBuilder()->addTaskList([
            $writeTask->text($arguments['text'] . \file_get_contents($arguments['file'])),
            $processTask,
        ])->run();
    }
}

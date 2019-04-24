<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

use PhpTaskman\Core\Robo\Task\FileEdition\LoadFileEditionTasks;
use PhpTaskman\Core\Robo\Task\ProcessConfigFile\LoadProcessConfigFileTasks;
use Robo\Common\BuilderAwareTrait;

final class PrependTask extends BaseTask
{
    use BuilderAwareTrait;
    use LoadFileEditionTasks;
    use LoadProcessConfigFileTasks;

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
        $arguments = $this->getTask();

        return $this->collectionBuilder()->addTaskList([
            $this->taskWritePrependToFile($arguments['file'])->prepend()->text($arguments['text']),
            $this->taskProcessConfigFile($arguments['file'], $arguments['file']),
        ])->run();
    }
}

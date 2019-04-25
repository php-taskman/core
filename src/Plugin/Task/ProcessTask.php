<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

use PhpTaskman\Core\Contract\ConfigurationTokensAwareInterface;
use PhpTaskman\Core\Plugin\BaseTask;
use PhpTaskman\Core\Traits\ConfigurationTokensTrait;
use Robo\Common\BuilderAwareTrait;
use Robo\Common\ResourceExistenceChecker;
use Robo\Contract\BuilderAwareInterface;
use Robo\Result;
use Robo\Task\File\loadTasks;
use Robo\Task\File\Replace;
use Robo\Task\Filesystem\FilesystemStack;

final class ProcessTask extends BaseTask implements BuilderAwareInterface, ConfigurationTokensAwareInterface
{
    use BuilderAwareTrait;
    use ConfigurationTokensTrait;
    use loadTasks;
    use ResourceExistenceChecker;

    public const ARGUMENTS = [
        'from',
        'to',
    ];
    public const NAME = 'process';

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $arguments = $this->getTaskArguments();

        $from = $arguments['from'];
        $to = $arguments['to'];

        $filesystem = new FilesystemStack();
        $replace = new Replace($to);

        if (!$this->checkResource($from, 'file')) {
            return Result::error($this, "Source file '{$from}' does not exists.");
        }

        $sourceContent = \file_get_contents($from);

        if (false === $sourceContent) {
            return Result::error($this, "Unable to read source file '{$from}'.");
        }

        $tokens = $this->extractProcessedTokens($sourceContent);

        $tasks = [];
        if ($from !== $to) {
            $tasks[] = $filesystem->copy($from, $to, true);
        }
        $tasks[] = $replace->from(\array_keys($tokens))->to(\array_values($tokens));

        return $this->collectionBuilder()->addTaskList($tasks)->run();
    }
}

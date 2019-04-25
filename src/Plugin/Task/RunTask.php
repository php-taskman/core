<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

use PhpTaskman\Core\Plugin\BaseTask;
use Robo\Common\BuilderAwareTrait;
use Robo\Exception\TaskException;
use Robo\Robo;
use Robo\Task\Base\loadTasks;

final class RunTask extends BaseTask
{
    use BuilderAwareTrait;
    use loadTasks;

    public const ARGUMENTS = [];
    public const NAME = 'run';

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $arguments = $this->getTaskArguments();

        $bin = \realpath($this->getConfig()->get('taskman.bin_dir') . '/taskman');

        if (false === $bin) {
            throw new TaskException(__CLASS__, 'Unable to find the taskman binary');
        }

        $taskExec = $this->taskExec($bin)->arg($arguments['command']);

        $container = Robo::getContainer();

        /** @var \Robo\Application $app */
        $app = $container->get('application');

        /** @var \Consolidation\AnnotatedCommand\AnnotatedCommand $command */
        $command = $app->get($arguments['command']);
        $commandOptions = $command->getDefinition()->getOptions();

        // Propagate any input option passed to the child command.
        foreach ($arguments['options'] as $name => $values) {
            if (!isset($commandOptions[$name])) {
                continue;
            }

            // But only if the called command has this option.
            foreach ((array) $values as $value) {
                $taskExec->option($name, $value);
            }
        }

        return $taskExec->run();
    }
}

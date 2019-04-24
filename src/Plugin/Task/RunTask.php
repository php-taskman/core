<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

use PhpTaskman\Core\Robo\Task\FileEdition\LoadFileEditionTasks;
use PhpTaskman\Core\Robo\Task\ProcessConfigFile\LoadProcessConfigFileTasks;
use Robo\Common\BuilderAwareTrait;
use Robo\Robo;
use Robo\Task\Base\loadTasks;

final class RunTask extends BaseTask
{
    use BuilderAwareTrait;
    use LoadFileEditionTasks;
    use LoadProcessConfigFileTasks;
    use loadTasks;
    public const ARGUMENTS = [];

    public const NAME = 'run';

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $arguments = $this->getTask();

        $taskExec = $this->taskExec(
            $this->getConfig()->get('taskman.bin_dir') . '/taskman'
        )->arg($arguments['command']);

        $container = Robo::getContainer();

        /** @var \Robo\Application $app */
        $app = $container->get('application');

        /** @var \Consolidation\AnnotatedCommand\AnnotatedCommand $command */
        $command = $app->get($arguments['command']);
        $commandOptions = $command->getDefinition()->getOptions();

        // Propagate any input option passed to the child command.
        foreach ($this->getOptions() as $name => $values) {
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

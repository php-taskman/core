<?php

namespace PhpTaskman\Core\Robo\Task\CollectionFactory;

use PhpTaskman\Core\Contract\ConfigurationTokensAwareInterface;
use PhpTaskman\Core\Robo\Task\Filesystem\LoadFilesystemTasks;
use PhpTaskman\Core\Robo\Task\ProcessConfigFile\LoadProcessConfigFileTasks;
use PhpTaskman\Core\Traits\ConfigurationTokensTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Contract\SimulatedInterface;
use Robo\Exception\TaskException;
use Robo\LoadAllTasks;
use Robo\Robo;
use Robo\Task\BaseTask;
use Symfony\Component\Yaml\Yaml;

/**
 * Class CollectionFactory.
 *
 * Return a task collection given its array representation.
 */
final class CollectionFactory extends BaseTask implements
    BuilderAwareInterface,
    SimulatedInterface,
    ConfigurationTokensAwareInterface
{
    use ConfigurationTokensTrait;
    use LoadAllTasks;
    use LoadFilesystemTasks;
    use LoadProcessConfigFileTasks;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $tasks;

    /**
     * CollectionFactory constructor.
     *
     * @param array $tasks
     * @param array $options
     */
    public function __construct(array $tasks = [], array $options = [])
    {
        $this->tasks = $tasks;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return isset($this->tasks['help']) ? $this->tasks['help'] : 'Yaml command defined in tasks.yml';
    }

    /**
     * @return array
     */
    public function getTasks()
    {
        return empty($this->tasks['tasks']) ? $this->tasks : $this->tasks['tasks'];
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $collection = $this->collectionBuilder();

        foreach ($this->getTasks() as $task) {
            if (\is_string($task)) {
                $collection->addTask($this->taskExec($task));

                continue;
            }

            if (!\is_array($task)) {
                // Todo: Error.
                continue;
            }

            if (!isset($task['task'])) {
                // Todo: Error.
                continue;
            }

            if (!\is_string($task['task'])) {
                // Todo: Error.
                continue;
            }

            $collection->addTask($this->taskFactory($task));
        }

        return $collection->run();
    }

    /**
     * {@inheritdoc}
     */
    public function simulate($context)
    {
        foreach ($this->getTasks() as $task) {
            if (\is_array($task)) {
                $task = Yaml::dump($task, 0);
            }

            $this->printTaskInfo($task, $context);
        }
    }

    /**
     * Secure option value.
     *
     * @param array  $task
     * @param string $name
     * @param mixed  $default
     */
    protected function secureOption(array &$task, $name, $default)
    {
        $task[$name] = isset($task[$name]) ? $task[$name] : $default;
    }

    /**
     * @param array|string $task
     *
     * @throws \Robo\Exception\TaskException
     *
     * @return \Robo\Contract\TaskInterface
     *
     * @SuppressWarnings(PHPMD)
     */
    protected function taskFactory($task)
    {
        $this->secureOption($task, 'force', false);
        $this->secureOption($task, 'umask', 0000);
        $this->secureOption($task, 'recursive', false);
        $this->secureOption($task, 'time', \time());
        $this->secureOption($task, 'atime', \time());
        $this->secureOption($task, 'mode', 0777);

        $taskMap = [
            'mkdir' => [
                'factory' => 'taskFilesystemFactory',
                'command' => 'mkdir',
                'parameters' => [
                    'dir',
                    'mode',
                ],
            ],
            'touch' => [
                'factory' => 'taskFilesystemFactory',
                'command' => 'touch',
                'parameters' => [
                    'file',
                    'time',
                    'atime',
                ],
            ],
            'copy' => [
                'factory' => 'taskFilesystemFactory',
                'command' => 'copy',
                'parameters' => [
                    'from',
                    'to',
                    'force',
                ],
            ],
            'chmod' => [
                'factory' => 'taskFilesystemFactory',
                'command' => 'chmod',
                'parameters' => [
                    'file',
                    'permissions',
                    'umask',
                    'recursive',
                ],
            ],
            'chgrp' => [
                'factory' => 'taskFilesystemFactory',
                'command' => 'chgrp',
                'parameters' => [
                    'file',
                    'group',
                    'recursive',
                ],
            ],
            'chown' => [
                'factory' => 'taskFilesystemFactory',
                'command' => 'chown',
                'parameters' => [
                    'file',
                    'user',
                    'recursive',
                ],
            ],
            'remove' => [
                'factory' => 'taskFilesystemFactory',
                'command' => 'remove',
                'parameters' => [
                    'file',
                ],
            ],
            'rename' => [
                'factory' => 'taskFilesystemFactory',
                'command' => 'rename',
                'parameters' => [
                    'from',
                    'to',
                    'force',
                ],
            ],
            'symlink' => [
                'factory' => 'taskFilesystemFactory',
                'command' => 'symlink',
                'parameters' => [
                    'from',
                    'to',
                ],
            ],
            'mirror' => [
                'factory' => 'taskFilesystemFactory',
                'command' => 'mirror',
                'parameters' => [
                    'from',
                    'to',
                ],
            ],
        ];

        if (isset($taskMap[$task['task']])) {
            $parameters = [];

            foreach ($taskMap[$task['task']]['parameters'] as $key) {
                if (isset($task[$key])) {
                    $parameters[$key] = $task[$key];
                }
            }

            return $this->{$taskMap[$task['task']]['factory']}($task['task'], $parameters);
        }

        switch ($task['task']) {
            case 'process':
                return $this->collectionBuilder()->addTaskList([
                    $this->taskProcessConfigFile($task['source'], $task['destination']),
                ]);

            case 'append':
                return $this->collectionBuilder()->addTaskList([
                    $this->taskWriteToFile($task['file'])->append()->text($task['text']),
                    $this->taskProcessConfigFile($task['file'], $task['file']),
                ]);

            case 'run':
                $taskExec = $this->taskExec(
                    $this->getConfig()->get('taskman.bin_dir') . '/taskman'
                )->arg($task['command']);

                $container = Robo::getContainer();

                /** @var \Robo\Application $app */
                $app = $container->get('application');

                /** @var \Consolidation\AnnotatedCommand\AnnotatedCommand $command */
                $command = $app->get($task['command']);
                $commandOptions = $command->getDefinition()->getOptions();

                // Propagate any input option passed to the child command.
                foreach ($this->options as $name => $values) {
                    if (!isset($commandOptions[$name])) {
                        continue;
                    }

                    // But only if the called command has this option.
                    foreach ((array) $values as $value) {
                        $taskExec->option($name, $value);
                    }
                }

                return $taskExec;
            default:
                throw new TaskException($this, "Task '{$task['task']}' not supported.");
        }
    }
}

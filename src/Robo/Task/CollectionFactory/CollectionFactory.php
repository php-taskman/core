<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Robo\Task\CollectionFactory;

use PhpTaskman\Core\Contract\ConfigurationTokensAwareInterface;
use PhpTaskman\Core\Robo\Task\FileEdition\LoadFileEditionTasks;
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
    use LoadFileEditionTasks;
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
        return $this->tasks['help'] ?? 'Yaml command defined in tasks.yml';
    }

    /**
     * @return array
     */
    public function getTasks()
    {
        return $this->tasks['tasks'] ?? $this->tasks;
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
        $task[$name] = $task[$name] ?? $default;
    }

    /**
     * @param array $task
     *
     * @throws \Robo\Exception\TaskException
     *
     * @return \Robo\Contract\TaskInterface
     */
    protected function taskFactory(array $task)
    {
        $this->secureOption($task, 'force', false);
        $this->secureOption($task, 'umask', 0000);
        $this->secureOption($task, 'recursive', false);
        $this->secureOption($task, 'time', \time());
        $this->secureOption($task, 'atime', \time());
        $this->secureOption($task, 'mode', 0777);

        if (!Robo::getContainer()->has('task.' . $task['task'])) {
            throw new TaskException($this, 'Unkown task: ' . $task['task']);
        }

        /** @var \PhpTaskman\Core\Contract\TaskInterface $taskFactory */
        $taskFactory = Robo::getContainer()->get('task.' . $task['task']);
        $taskFactory->setTask($task);
        $taskFactory->setOptions($this->options);

        return $this
            ->collectionBuilder()
            ->addTaskList([
                $taskFactory,
            ]);
    }
}

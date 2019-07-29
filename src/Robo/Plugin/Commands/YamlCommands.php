<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Robo\Plugin\Commands;

use Consolidation\AnnotatedCommand\AnnotatedCommand;
use PhpTaskman\CoreTasks\Plugin\Task\CollectionFactoryTask;
use Robo\Collection\CollectionBuilder;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\Exception\TaskException;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * Class DynamicCommands.
 */
final class YamlCommands extends AbstractCommands
{
    /**
     * Bind input values of custom command options to config entries.
     *
     * @param \Symfony\Component\Console\Event\ConsoleCommandEvent $event
     *
     * @hook pre-command-event *
     */
    public function bindInputOptionsToConfig(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();

        if (null === $command) {
            return;
        }

        if (AnnotatedCommand::class !== \get_class($command)) {
            return;
        }

        if (!($command instanceof AnnotatedCommand)) {
            return;
        }

        /** @var \Consolidation\AnnotatedCommand\AnnotatedCommand $command */
        /** @var \Consolidation\AnnotatedCommand\AnnotationData $annotatedData */
        $annotatedData = $command->getAnnotationData();

        if (!$annotatedData->get('dynamic-command')) {
            return;
        }

        // Dynamic commands may define their own options bound to specific configuration. Dynamically set the
        // configuration from command options.
        $config = $this->getConfig();
        $commands = $config->get('commands');

        if (empty($commands[$command->getName()]['options'])) {
            return;
        }

        foreach ($commands[$command->getName()]['options'] as $optionName => $option) {
            if (empty($option['config']) && $event->getInput()->hasOption($optionName)) {
                continue;
            }

            $inputValue = $event->getInput()->getOption($optionName);

            if (null === $inputValue) {
                continue;
            }

            $config->set(
                $option['config'],
                $event->getInput()->getOption($optionName)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile(): string
    {
        return __DIR__ . '/../../../../config/commands/default.yml';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConfigurationFile(): string
    {
        return __DIR__ . '/../../../../config/default.yml';
    }

    /**
     * Run a task.
     *
     * @dynamic-command true
     *
     * @throws \Robo\Exception\TaskException
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function runTasks(): CollectionBuilder
    {
        $command = $this->input()->getArgument('command');

        if (!\is_string($command)) {
            throw new TaskException($this, 'The command must be a string.');
        }

        $inputOptions = [];

        foreach ($this->input()->getOptions() as $name => $value) {
            if ($this->input()->hasParameterOption('--' . $name)) {
                $inputOptions[$name] = $value;
            }
        }

        $command = $this->getConfig()->get('commands.' . $command);

        // Handle different types of command definitions.
        if (isset($command['tasks'])) {
            $arguments = [
                'tasks' => $command['tasks'],
                'options' => $inputOptions,
                'preconditions' => $command['preconditions'] ?? [],
            ];

            if (\is_string($arguments['preconditions'])) {
                $arguments['preconditions'] = [$arguments['preconditions']];
            }

            /** @var CollectionFactoryTask $preconditionsTask */
            $preconditionsTask = $this->task(CollectionFactoryTask::class);
            $preconditionsTask->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_DEBUG);
            $preconditionsTask->setTaskArguments([
                'tasks' => $arguments['preconditions'],
                'options' => [],
            ]);

            if (false === $preconditionsTask->run()->wasSuccessful()) {
                $arguments['tasks'] = [];
            }
        } else {
            $arguments = [
                'tasks' => $command,
                'options' => [],
            ];
        }

        /** @var CollectionFactoryTask $collectionFactory */
        $collectionFactory = $this->task(CollectionFactoryTask::class);

        return $collectionFactory->setTaskArguments($arguments);
    }
}

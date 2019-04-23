<?php

namespace PhpTaskman\Core;

use Composer\Autoload\ClassLoader;
use Consolidation\AnnotatedCommand\AnnotatedCommand;
use PhpTaskman\Core\Robo\Plugin\Commands\YamlCommands;
use League\Container\ContainerAwareTrait;
use Robo\Application;
use Robo\Common\ConfigAwareTrait;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Runner.
 */
final class Runner
{
    use ConfigAwareTrait;
    use ContainerAwareTrait;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var \Composer\Autoload\ClassLoader
     */
    private $classLoader;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var \Robo\Runner
     */
    private $runner;

    /**
     * @var string
     */
    private $workingDir;

    /**
     * Runner constructor.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param ClassLoader $classLoader
     */
    public function __construct(
        InputInterface $input = null,
        OutputInterface $output = null,
        ClassLoader $classLoader = null
    ) {
        $this->input = $input ?? new ArgvInput();
        $this->output = $output ?? new ConsoleOutput();
        $this->classLoader = $classLoader ?? new ClassLoader();

        $this->workingDir = $this->getWorkingDir($this->input);
        \chdir($this->workingDir);

        $this->config = Taskman::createConfiguration(
            [],
            $this->workingDir
        );
        $this->application = Taskman::createDefaultApplication(
            null,
            null,
            $this->workingDir
        );
        $this->container = Taskman::createContainer(
            $this->input,
            $this->output,
            $this->application,
            $this->config,
            $this->classLoader
        );

        $this->runner = Taskman::createDefaultRunner($this->container);
    }

    /**
     * @param mixed $args
     *
     * @return int
     */
    public function run($args)
    {
        // Register command classes.
        $this->runner->registerCommandClasses($this->application, [YamlCommands::class]);

        // Register commands defined in task.yml file.
        $this->registerDynamicCommands($this->application);

        // Run command.
        return $this->runner->run($this->input, $this->output, $this->application);
    }

    /**
     * @param bool $hasDefault
     * @param mixed $defaultValue
     *
     * @return int
     */
    protected function getCommandArgumentMode(bool $hasDefault, $defaultValue)
    {
        if (!$hasDefault) {
            return InputArgument::REQUIRED;
        }
        if (\is_array($defaultValue)) {
            return InputArgument::IS_ARRAY;
        }

        return InputArgument::OPTIONAL;
    }

    /**
     * @param \Consolidation\AnnotatedCommand\AnnotatedCommand $command
     * @param array $commandDefinition
     */
    private function addOptions(AnnotatedCommand $command, array $commandDefinition)
    {
        // This command doesn't define any option.
        if (empty($commandDefinition['options'])) {
            return;
        }

        $defaults = \array_fill_keys(['shortcut', 'mode', 'description', 'default'], null);
        foreach ($commandDefinition['options'] as $optionName => $optionDefinition) {
            $optionDefinition += $defaults;
            $command->addOption(
                '--' . $optionName,
                $optionDefinition['shortcut'],
                $optionDefinition['mode'],
                $optionDefinition['description'],
                $optionDefinition['default']
            );
        }
    }

    /**
     * @param string $command
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    private function getTasks($command)
    {
        $commands = $this->getConfig()->get('commands', []);

        if (!isset($commands[$command])) {
            throw new \InvalidArgumentException("Custom command '${command}' not defined.");
        }

        return !empty($commands[$command]['tasks']) ? $commands[$command]['tasks'] : $commands[$command];
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *
     * @return mixed
     */
    private function getWorkingDir(InputInterface $input)
    {
        return $input->getParameterOption('--working-dir', \getcwd());
    }

    /**
     * @param \Robo\Application $application
     */
    private function registerDynamicCommands(Application $application)
    {
        $customCommands = $this->getConfig()->get('commands', []);
        foreach ($customCommands as $name => $commandDefinition) {
            /** @var \Consolidation\AnnotatedCommand\AnnotatedCommandFactory $commandFactory */
            $commandFileName = YamlCommands::class . 'Commands';
            $commandClass = $this->container->get($commandFileName);
            $commandFactory = $this->container->get('commandFactory');
            $commandInfo = $commandFactory->createCommandInfo($commandClass, 'runTasks');

            $commandDefinition += ['options' => []];
            foreach ($commandDefinition['options'] as &$option) {
                if (isset($option['mode'])) {
                    continue;
                }

                $option['mode'] = $this->getCommandArgumentMode(
                    isset($option['default']),
                    $option['default'] ?? null
                );
            }

            $command = $commandFactory->createCommand($commandInfo, $commandClass)->setName($name);

            // Dynamic commands may define their own options.
            $this->addOptions($command, $commandDefinition);

            // Append also options of subsequent tasks.
            foreach ($this->getTasks($name) as $taskEntry) {
                if (!\is_array($taskEntry)) {
                    continue;
                }

                if (!isset($taskEntry['task'])) {
                    continue;
                }

                if ('run' !== $taskEntry['task']) {
                    continue;
                }

                if (empty($taskEntry['command'])) {
                    continue;
                }

                // This is a 'run' task.
                if (!empty($customCommands[$taskEntry['command']])) {
                    // Add the options of another custom command.
                    $this->addOptions($command, $customCommands[$taskEntry['command']]);
                } else {
                    // Add the options of an already registered command.
                    if ($this->application->has($taskEntry['command'])) {
                        $subCommand = $this->application->get($taskEntry['command']);
                        $command->addOptions($subCommand->getDefinition()->getOptions());
                    }
                }
            }

            $application->add($command);
        }
    }
}

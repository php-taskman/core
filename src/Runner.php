<?php

namespace PhpTaskman\Core;

use Composer\Autoload\ClassLoader;
use Consolidation\AnnotatedCommand\AnnotatedCommand;
use League\Container\ContainerAwareTrait;
use League\Container\Inflector\Inflector;
use PhpTaskman\Core\Robo\Plugin\Commands\YamlCommands;
use PhpTaskman\CoreTasks\Plugin\Task\YamlTask;
use Robo\Application;
use Robo\Collection\CollectionBuilder;
use Robo\Common\ConfigAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Tasks;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
     *
     * @throws \Exception
     */
    public function __construct(
        InputInterface $input = null,
        OutputInterface $output = null,
        ClassLoader $classLoader = null
    ) {
        $this->input = null === $input ? new ArgvInput() : $input;
        $this->output = null === $output ? new ConsoleOutput() : $output;
        $this->classLoader = null === $classLoader ? new ClassLoader() : $classLoader;

        $this->config = Taskman::createConfiguration(
            []
        );

        $this->application = Taskman::createDefaultApplication();

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
     * @throws \ReflectionException
     *
     * @return int
     */
    public function run($args)
    {
        // Register command classes.
        $this->runner->registerCommandClasses($this->application, [YamlCommands::class]);

        // Register commands defined in task.yml file.
        $this->registerDynamicCommands($this->application);

        // Register tasks
        $this->registerDynamicTasks($this->application);

        // Register global options.
        $this->registerGlobalCommandOptions($this->application);

        // Run command.
        return $this->runner->run($this->input, $this->output, $this->application);
    }

    /**
     * @param bool $hasDefault
     * @param mixed $defaultValue
     *
     * @return int
     */
    protected function getCommandArgumentMode($hasDefault, $defaultValue)
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

        $defaults = array_fill_keys(['shortcut', 'mode', 'description', 'default'], null);

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
     * @param \Robo\Application $application
     */
    private function registerDynamicCommands(Application $application)
    {
        $commandDefinitions = $this->getConfig()->get('commands', null);

        if (null === $commandDefinitions) {
            return;
        }

        foreach ($commandDefinitions as $name => $commandDefinition) {
            /** @var \PhpTaskman\Core\Robo\Plugin\Commands\YamlCommands $commandClass */
            $commandClass = $this->container->get(YamlCommands::class . 'Commands');

            /** @var \Consolidation\AnnotatedCommand\AnnotatedCommandFactory $commandFactory */
            $commandFactory = $this->container->get('commandFactory');

            $commandInfo = $commandFactory->createCommandInfo($commandClass, 'runTasks');

            $commandDefinition += ['options' => []];

            foreach ($commandDefinition['options'] as &$option) {
                if (isset($option['mode'])) {
                    continue;
                }

                $option['mode'] = $this->getCommandArgumentMode(
                    isset($option['default']),
                    isset($option['default']) ? $option['default'] : null
                );
            }

            $command = $commandFactory->createCommand($commandInfo, $commandClass)->setName($name);

            // Override default description.
            if (isset($commandDefinition['description'])) {
                $command->setDescription($commandDefinition['description']);
            }
            // Override default help.
            if (isset($commandDefinition['help'])) {
                $command->setHelp($commandDefinition['help']);
            }

            // Dynamic commands may define their own options.
            $this->addOptions($command, $commandDefinition);

            $tasks = isset($commandDefinition['tasks']) ? $commandDefinition['tasks'] : $commandDefinition;

            // Append also options of subsequent tasks.
            foreach ($tasks as $taskEntry) {
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

    /**
     * @param \Robo\Application $application
     *
     * @throws \ReflectionException
     */
    private function registerDynamicTasks(Application $application)
    {
        $classes = Taskman::discoverTasksClasses('Plugin');

        /** @var \ReflectionClass[] $tasks */
        $tasks = [];

        foreach ($classes as $className) {
            $class = new \ReflectionClass($className);

            if (!$class->isInstantiable()) {
                continue;
            }

            $tasks[] = $class;
        }

        $builder = CollectionBuilder::create($this->container, new Tasks());

        $inflector = $this->container->inflector(BuilderAwareInterface::class);

        if ($inflector instanceof Inflector) {
            $inflector->invokeMethod('setBuilder', [$builder]);
        }

        // Register custom Task classes.
        foreach ($tasks as $taskReflectionClass) {
            $this->container->add(
                'task.' . $taskReflectionClass->getConstant('NAME'),
                $taskReflectionClass->getName()
            );
        }

        // Register custom YAML tasks.
        $customTasks = $this->getConfig()->get('tasks', null);

        if (null === $customTasks) {
            return;
        }

        foreach ($customTasks as $name => $tasks) {
            $this->container->add(
                'task.' . $name,
                YamlTask::class
            )->withArgument($tasks);
        }
    }

    /**
     * Register the global commands options.
     *
     * @param \Robo\Application $application
     */
    private function registerGlobalCommandOptions(Application $application)
    {
        $globalOptions = $this->config->get('globals.options', null);

        if (null === $globalOptions) {
            return;
        }

        $config = $this->getConfig();

        foreach ($globalOptions as $option => $optionDefinition) {
            $optionMachineName = 'options.' . ($optionDefinition['config'] ?? $option);

            $optionDefinition += [
                'default' => null,
            ];

            $optionDefinition['default'] = $this->input->getParameterOption(
                '--' . $option,
                $optionDefinition['default']
            );

            // Special handling for the working-dir option.
            if ('working-dir' === $option) {
                if (null === $optionDefinition['default']) {
                    $optionDefinition['default'] = getcwd();
                }

                $optionDefinition['default'] = realpath($optionDefinition['default']);
            }

            $config->set($optionMachineName, $optionDefinition['default']);

            $optionDefinition += [
                'mode' => InputOption::VALUE_OPTIONAL,
                'description' => '',
                'shortcut' => [],
            ];

            $optionDefinition['shortcut'] = (array) $optionDefinition['shortcut'];

            $application
                ->getDefinition()
                ->addOption(
                    new InputOption(
                        '--' . $option,
                        $optionDefinition['shortcut'],
                        $optionDefinition['mode'],
                        $optionDefinition['description'],
                        $optionDefinition['default']
                    )
                );
        }
    }
}

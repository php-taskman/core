<?php

namespace PhpTaskman\Core;

use Composer\Autoload\ClassLoader;
use Consolidation\Config\ConfigInterface;
use Consolidation\Config\Loader\ConfigProcessor;
use League\Container\Container;
use League\Container\ContainerInterface;
use PhpTaskman\Core\Config\Loader\JsonConfigLoader;
use Robo\Application;
use Robo\Config\Config;
use Robo\Robo;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Taskman.
 */
final class Taskman
{
    const APPLICATION_NAME = 'Taskman';
    const VERSION = 'dev-master';

    /**
     * Create default configuration.
     *
     * @param null|mixed $workingDir
     * @param mixed $paths
     *
     * @return \Consolidation\Config\ConfigInterface
     */
    public static function createConfiguration($paths, $workingDir = null)
    {
        $workingDir = null === $workingDir ? \getcwd() : $workingDir;

        // Create a default configuration.
        $config = Robo::createConfiguration($paths);

        // Set the variable working_dir.
        if (false === $workingDir = \realpath($workingDir)) {
            return $config;
        }

        $config->set('taskman.working_dir', $workingDir);

        $paths = \PhpTaskman\Core\Config\Config::findFilesToIncludeInConfiguration($workingDir);

        // Load the configuration.
        Robo::loadConfiguration(
            $paths,
            $config
        );

        return $config;
    }

    /**
     * Create and configure container.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Application $application
     * @param ConfigInterface $config
     * @param ClassLoader $classLoader
     *
     * @return Container|\League\Container\ContainerInterface
     */
    public static function createContainer(
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        ConfigInterface $config,
        ClassLoader $classLoader
    ) {
        $container = Robo::createDefaultContainer($input, $output, $application, $config, $classLoader);
        $container->get('commandFactory')->setIncludeAllPublicMethods(false);

        return $container;
    }

    /**
     * @param null|string $appName
     * @param null|string $appVersion
     * @param null|string $workingDir
     *
     * @return Application
     */
    public static function createDefaultApplication($appName = null, $appVersion = null, $workingDir = null)
    {
        $appName = null === $appName ? self::APPLICATION_NAME : $appName;
        $appVersion = null === $appVersion ? self::VERSION : $appVersion;

        $app = Robo::createDefaultApplication($appName, $appVersion);

        if (null === $workingDir || false === $workingDir = \realpath($workingDir)) {
            $workingDir = \getcwd();
        }

        $app
            ->getDefinition()
            ->addOption(
                new InputOption(
                    '--working-dir',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Working directory, defaults to current working directory.',
                    $workingDir
                )
            );

        $app->setAutoExit(false);

        return $app;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return \Robo\Runner
     */
    public static function createDefaultRunner(ContainerInterface $container)
    {
        return (new \Robo\Runner())
            ->setRelativePluginNamespace('Robo\Plugin')
            ->setContainer($container);
    }

    /**
     * @param string[] $paths
     *   Array of JSON filepaths.
     *
     * @return Config
     *   A config object.
     */
    public static function createJsonConfiguration(array $paths)
    {
        $config = new Config();
        self::loadJsonConfiguration($paths, $config);

        return $config;
    }

    /**
     * @param string $relativeNamespace
     *
     * @return array|string[]
     */
    public static function discoverTasksClasses($relativeNamespace)
    {
        /** @var \Robo\ClassDiscovery\RelativeNamespaceDiscovery $discovery */
        $discovery = Robo::service('relativeNamespaceDiscovery');
        $discovery->setRelativeNamespace($relativeNamespace . '\Task')
            ->setSearchPattern('*Task.php');

        return $discovery->getClasses();
    }

    /**
     * @param string[] $paths
     *   Array of JSON filepaths.
     * @param null|Config $config
     *   A config object.
     */
    public static function loadJsonConfiguration(array $paths, Config $config = null)
    {
        if (null === $config) {
            $config = Robo::config();
        }

        $loader = new JsonConfigLoader();
        $processor = new ConfigProcessor();
        $processor->add($config->export());

        foreach ($paths as $path) {
            $processor->extend($loader->load($path));
        }

        $config->import($processor->export());
    }
}

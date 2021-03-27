<?php

declare(strict_types=1);

namespace PhpTaskman\Core;

use Composer\Autoload\ClassLoader;
use Consolidation\Config\ConfigInterface;
use Consolidation\Config\Loader\ConfigProcessor;
use Exception;
use League\Container\Container;
use PhpTaskman\Core\Config\Config;
use PhpTaskman\Core\Config\Loader\JsonConfigLoader;
use Psr\Container\ContainerInterface;
use Robo\Application;
use Robo\Config\Config as RoboConfig;
use Robo\Robo;
use Robo\Runner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Taskman
{
    public const APPLICATION_NAME = 'Taskman';

    public const VERSION = '@git_commit_short@';

    /**
     * Create default configuration.
     *
     * @param mixed $paths
     *
     * @return \Consolidation\Config\ConfigInterface
     */
    public static function createConfiguration(array $paths = [])
    {
        // Create a default configuration.
        $config = Robo::createConfiguration($paths);

        if (false !== $cwd = getcwd()) {
            $paths = Config::findFilesToIncludeInConfiguration($cwd);
        }

        // Load the configuration.
        Robo::loadConfiguration(
            $paths,
            $config
        );

        [$scriptPath] = get_included_files();
        $config->set('options.bin', $scriptPath);

        return $config;
    }

    /**
     * Create and configure container.
     */
    public static function createContainer(
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        ConfigInterface $config,
        ClassLoader $classLoader
    ): ContainerInterface {
        $container = new Container();

        Robo::configureContainer($container, $application, $config, $input, $output, $classLoader);

        return $container;
    }

    public static function createDefaultApplication(
        string $appName,
        string $appVersion,
        ?string $workingDir = null
    ): Application {
        $app = Robo::createDefaultApplication($appName, $appVersion);

        $app->setAutoExit(false);

        return $app;
    }

    /**
     * @throws Exception
     */
    public static function createDefaultRunner(ContainerInterface $container): Runner
    {
        $cwd = getcwd();

        $workingDir = $container->get('input')->getParameterOption('--working-dir', $cwd);

        if (null === $workingDir) {
            $workingDir = $cwd;
        }

        if (false === realpath($workingDir)) {
            throw new Exception(sprintf('Working directory "%s" does not exists.', $workingDir));
        }

        return (new Runner())
            ->setRelativePluginNamespace('Robo\Plugin')
            ->setContainer($container);
    }

    /**
     * @param string[] $paths
     *   Array of JSON filepaths.
     */
    public static function createJsonConfiguration(array $paths): ConfigInterface
    {
        $config = new RoboConfig();

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
     * @param ConfigInterface|null $config
     *   A config object.
     */
    public static function loadJsonConfiguration(array $paths, ?ConfigInterface $config): void
    {
        if (null === $config) {
            // This needs to be removed when Robo will have the method replace()
            // in the ConfigInterface interface.
            /** @var Config $config */
            $config = Robo::config();
        }

        $loader = new JsonConfigLoader();
        $processor = new ConfigProcessor();
        $processor->add($config->export());

        foreach ($paths as $path) {
            $processor->extend($loader->load($path));
        }

        $config->replace($processor->export());
    }
}

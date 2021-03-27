<?php

declare(strict_types=1);

namespace PhpTaskman\Core;

use Composer\Autoload\ClassLoader;
use Consolidation\Config\ConfigInterface;
use Consolidation\Config\Loader\ConfigProcessor;
use Exception;
use PhpTaskman\Core\Config\Loader\JsonConfigLoader;
use Psr\Container\ContainerInterface;
use Robo\Application;
use Robo\Config\Config;
use Robo\Robo;

final class Taskman
{
    public const APPLICATION_NAME = 'Taskman';

    public const VERSION = 'dev-master';

    public static function createConfiguration(array $paths = []): ConfigInterface
    {
        // Create a default configuration.
        $config = Robo::createConfiguration($paths);

        if (false !== $cwd = getcwd()) {
            $paths = \PhpTaskman\Core\Config\Config::findFilesToIncludeInConfiguration($cwd);
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

    public static function createContainer(
        Application $application,
        ConfigInterface $config,
        ClassLoader $classLoader
    ): ContainerInterface {
        $container = Robo::createContainer($application, $config, $classLoader);
        $container->get('commandFactory')->setIncludeAllPublicMethods(false);

        return $container;
    }

    /**
     * @param string|null $appName
     * @param string|null $appVersion
     * @param string|null $workingDir
     *
     * @return Application
     */
    public static function createDefaultApplication($appName = null, $appVersion = null, $workingDir = null)
    {
        $appName = $appName ?? self::APPLICATION_NAME;
        $appVersion = $appVersion ?? self::VERSION;

        $app = Robo::createDefaultApplication($appName, $appVersion);

        $app->setAutoExit(false);

        return $app;
    }

    /**
     * @throws Exception
     */
    public static function createDefaultRunner(ContainerInterface $container)
    {
        $cwd = getcwd();

        $workingDir = $container->get('input')->getParameterOption('--working-dir', $cwd);

        if (null === $workingDir) {
            $workingDir = $cwd;
        }

        if (false === realpath($workingDir)) {
            throw new Exception(sprintf('Working directory "%s" does not exists.', $workingDir));
        }

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
     * @param Config|null $config
     *   A config object.
     */
    public static function loadJsonConfiguration(array $paths, ?Config $config): void
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

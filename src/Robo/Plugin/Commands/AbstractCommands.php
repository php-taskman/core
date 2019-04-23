<?php

namespace PhpTaskman\Core\Robo\Plugin\Commands;

use Consolidation\Config\Loader\ConfigProcessor;
use PhpTaskman\Core\Taskman;
use Robo\Common\ConfigAwareTrait;
use Robo\Common\IO;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Robo\Contract\BuilderAwareInterface;
use Robo\LoadAllTasks;
use Robo\Robo;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class AbstractCommands.
 */
abstract class AbstractCommands implements
    BuilderAwareInterface,
    IOAwareInterface,
    ConfigAwareInterface
{
    use ConfigAwareTrait;
    use IO;
    use LoadAllTasks;

    /**
     * Path to YAML configuration file containing command defaults.
     *
     * Command classes should implement this method.
     *
     * @return string
     */
    abstract public function getConfigurationFile();

    /**
     * Path to YAML configuration file containing command defaults.
     *
     * Command classes should implement this method.
     *
     * @return string
     *   The path of the default configuration file.
     */
    abstract public function getDefaultConfigurationFile();

    /**
     * Load default configuration.
     *
     * PHP Tasks does not allow to provide default configuration for
     * commands. In this hook we load Toolkit default configuration and re-apply
     * it back.
     *
     * @hook pre-command-event *
     */
    public function loadDefaultConfig(ConsoleCommandEvent $event)
    {
        $config = $this->getConfig();

        if (null === $config->get('taskman.bin_dir')) {
            if (null !== $composerConfig = Taskman::createJsonConfiguration([\getcwd() . '/composer.json'])) {
                // The COMPOSER_BIN_DIR environment takes precedence over the value
                // defined in composer.json config, if any. Default to ./vendor/bin.
                if (!$composerBinDir = \getenv('COMPOSER_BIN_DIR')) {
                    $composerBinDir = $composerConfig->get('bin-dir', './vendor/bin');
                }

                if (false === \strpos($composerBinDir, './')) {
                    $composerBinDir = './' . $composerBinDir;
                }

                $composerBinDir = \rtrim($composerBinDir, \DIRECTORY_SEPARATOR);
                $config->set('taskman.bin_dir', $composerBinDir);
            }
        }

        // Refactor this.
        $configurationFilePath = \realpath($this->getConfigurationFile());

        Robo::loadConfiguration([$configurationFilePath], $config);

        $default_config = Taskman::createConfiguration(
            [$this->getDefaultConfigurationFile()]
        );

        // Re-build configuration.
        $processor = new ConfigProcessor();
        $processor->add($default_config->export());
        $processor->add($config->export());

        // Import newly built configuration.
        $config->import($processor->export());
    }
}

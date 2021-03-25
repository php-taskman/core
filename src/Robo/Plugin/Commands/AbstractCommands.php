<?php

declare(strict_types=1);

namespace PhpTaskman\Core\Robo\Plugin\Commands;

use Consolidation\Config\Loader\ConfigProcessor;
use PhpTaskman\Core\Taskman;
use Robo\Common\ConfigAwareTrait;
use Robo\Common\IO;
use Robo\Contract\BuilderAwareInterface;
use Robo\Contract\ConfigAwareInterface;
use Robo\Contract\IOAwareInterface;
use Robo\LoadAllTasks;
use Robo\Robo;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\Event;

use const DIRECTORY_SEPARATOR;

abstract class AbstractCommands implements
    BuilderAwareInterface,
    ConfigAwareInterface,
    IOAwareInterface
{
    use ConfigAwareTrait;
    use IO;
    use LoadAllTasks;

    /**
     * Path to YAML configuration file containing command defaults.
     *
     * Command classes should implement this method.
     */
    abstract public function getConfigurationFile(): string;

    /**
     * Path to YAML configuration file containing command defaults.
     *
     * Command classes should implement this method.
     *
     * @return string
     *   The path of the default configuration file.
     */
    abstract public function getDefaultConfigurationFile(): string;

    /**
     * Load default configuration.
     *
     * PHP Tasks does not allow to provide default configuration for
     * commands. In this hook we load Toolkit default configuration and re-apply
     * it back.
     *
     * @hook pre-command-event *
     */
    public function loadDefaultConfig(ConsoleCommandEvent $event): void
    {
        /** @var \Robo\Config\Config $config */
        $config = $this->getConfig();

        if (null === $config->get('options.bin_dir')) {
            if (null !== $composerConfig = Taskman::createJsonConfiguration([getcwd() . '/composer.json'])) {
                // The COMPOSER_BIN_DIR environment takes precedence over the value
                // defined in composer.json config, if any. Default to ./vendor/bin.
                if (!$composerBinDir = getenv('COMPOSER_BIN_DIR')) {
                    $composerBinDir = $composerConfig->get('bin-dir', './vendor/bin');
                }

                if (false === mb_strpos($composerBinDir, './')) {
                    $composerBinDir = './' . $composerBinDir;
                }

                $composerBinDir = rtrim($composerBinDir, DIRECTORY_SEPARATOR);
                $config->set('options.bin_dir', $composerBinDir);
            }
        }

        Robo::loadConfiguration(
            array_filter([
                realpath($this->getConfigurationFile()),
            ]),
            $config
        );

        $default_config = Taskman::createConfiguration(
            [$this->getDefaultConfigurationFile()]
        );

        // Re-build configuration.
        $processor = new ConfigProcessor();
        $processor->add($default_config->export());
        $processor->add($config->export());

        // Import newly built configuration.
        $config->replace($processor->export());
    }
}

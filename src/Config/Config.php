<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Config;

use PhpTaskman\Core\Taskman;

final class Config
{
    /**
     * Find the files to include in the configuration.
     *
     * @param string $cwd
     *   The current working directory.
     *
     * @return string[]
     *   The list of all the YAML files to include.
     */
    public static function findFilesToIncludeInConfiguration($cwd)
    {
        // Check if composer.json exists.
        $composerPath = \realpath($cwd . '/composer.json');

        if (false === $composerPath) {
            return [];
        }

        // Get the vendor-bin property from the composer.json.
        $composerConfig = Taskman::createJsonConfiguration([$composerPath]);
        $vendorDir = $composerConfig->get('vendor-dir', $cwd . '/vendor');

        // Keep a reference of the default filename that we need to load from
        // each packages.
        $filesToLoad = [
            'taskman.yml.dist',
            'taskman.yml',
        ];

        // Load default paths.
        $filesystemPaths = [
            __DIR__ . '/../config/default.yml',
            __DIR__ . '/../default.yml',
            static::getLocalConfigurationFilepath(),
        ];

        // Check if composer.lock exists.
        $composerLockPath = \realpath($cwd . '/composer.lock');

        if (false === $composerLockPath) {
            return [];
        }

        $composerLockConfig = Taskman::createJsonConfiguration(
            [$composerLockPath]
        );

        // Get the dependencies packages directories.
        $packageDirectories = \array_filter(
            \array_map(
                static function ($package) use ($vendorDir) {
                    return \realpath($vendorDir . '/' . $package['name']);
                },
                \array_merge(
                    $composerLockConfig->get('packages', []),
                    $composerLockConfig->get('packages-dev', [])
                )
            )
        );

        $packageDirectories[] = $cwd;

        // Loop over each composer.json, deduct the package directory and probe for files to include.
        foreach ($packageDirectories as $packageDirectory) {
            foreach ($filesToLoad as $taskmanFile) {
                foreach ([$packageDirectory, $cwd] as $directory) {
                    $candidateFile = $directory . '/' . $taskmanFile;
                    $filesystemPaths[$candidateFile] = $candidateFile;
                }
            }

            $composerConfig = Taskman::createJsonConfiguration(
                [$packageDirectory . '/composer.json']
            );

            foreach ($composerConfig->get('extra.taskman.files', []) as $commandFile) {
                $filesystemPaths[$commandFile] = $commandFile;
                $commandFile = $packageDirectory . '/' . $commandFile;
                $filesystemPaths[$commandFile] = $commandFile;
            }
        }

        return \array_filter(
            static::resolveImports(...\array_values($filesystemPaths)),
            'file_exists'
        );
    }

    /**
     * Get the local configuration filepath.
     *
     * @param string $configuration_file
     *   The default filepath.
     *
     * @return null|string
     *   The local configuration file path, or null if it doesn't exist.
     */
    public static function getLocalConfigurationFilepath($configuration_file = 'phptaskman/taskman.yml')
    {
        if ($config = \getenv('PHPTASKMAN_CONFIG')) {
            return $config;
        }

        if ($config = \getenv('XDG_CONFIG_HOME')) {
            return $config . '/' . $configuration_file;
        }

        if ($home = \getenv('HOME')) {
            return \getenv('HOME') . '/.config/' . $configuration_file;
        }

        return null;
    }

    /**
     * Resolve YAML configurations files containing imports.
     *
     * Handles circular dependencies by ignoring them.
     *
     * @param string[] ...$filepaths
     *   A list of YML filepath to parse.
     *
     * @return string[]
     *   The list of all the YAML files to include.
     */
    public static function resolveImports(...$filepaths)
    {
        return (new YamlRecursivePathsFinder($filepaths))
            ->getAllPaths();
    }
}

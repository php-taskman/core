<?php

namespace PhpTaskman\Core\Robo\Task\ProcessConfigFile;

use PhpTaskman\Core\Contract\ConfigurationTokensAwareInterface;
use PhpTaskman\Core\Traits\ConfigurationTokensTrait;
use Robo\Common\BuilderAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Exception\TaskException;
use Robo\Result;
use Robo\Task\BaseTask;
use Robo\Task\File\Replace;
use Robo\Task\Filesystem\FilesystemStack;

/**
 * Class ProcessConfigFile.
 */
final class ProcessConfigFile extends BaseTask implements BuilderAwareInterface, ConfigurationTokensAwareInterface
{
    use BuilderAwareTrait;
    use ConfigurationTokensTrait;

    /**
     * Destination file.
     *
     * @var string
     */
    protected $destination;

    /**
     * @var FilesystemStack
     */
    protected $filesystem;

    /**
     * @var Replace
     */
    protected $replace;

    /**
     * Source file.
     *
     * @var string
     */
    protected $source;

    /**
     * ProcessConfigFile constructor.
     *
     * @param string $source
     * @param string $destination
     */
    public function __construct($source, $destination)
    {
        $this->source = $source;
        $this->destination = $destination;
        $this->filesystem = new FilesystemStack();
        $this->replace = new Replace($destination);
    }

    /**
     * @throws TaskException
     *
     * @return Result
     */
    public function run()
    {
        if (false === $fileContent = \file_get_contents($this->source)) {
            throw new TaskException($this, "Source file '{$this->source}' does not exists.");
        }

        $tokens = $this->extractProcessedTokens($fileContent);

        $tasks = [];
        if ($this->source !== $this->destination) {
            $tasks[] = $this->filesystem->copy($this->source, $this->destination, true);
        }
        $tasks[] = $this->replace->from(\array_keys($tokens))->to(\array_values($tokens));

        return $this->collectionBuilder()->addTaskList($tasks)->run();
    }
}

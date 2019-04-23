<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Robo\Task\FileEdition;

use Robo\Task\File\Write;

/**
 * Class Prepend.
 */
class Prepend extends Write
{
    /**
     * @var bool
     */
    private $prepend;

    /**
     * {@inheritdoc}
     */
    public function getContentsToWrite()
    {
        $content = $this->originalContents();

        if (true === $this->prepend) {
            $content = parent::getContentsToWrite() . $content;
        }

        return $content;
    }

    /**
     * @param bool $prepend
     *
     * @return \PhpTaskman\Core\Robo\Task\FileEdition\Prepend
     */
    public function prepend($prepend = true)
    {
        $this->prepend = $prepend;

        return $this;
    }
}

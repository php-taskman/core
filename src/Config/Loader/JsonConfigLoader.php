<?php

namespace PhpTaskman\Core\Config\Loader;

use Consolidation\Config\Loader\ConfigLoader;

class JsonConfigLoader extends ConfigLoader
{
    public function load($path)
    {
        $this->setSourceName($path);

        // We silently skip any nonexistent config files, so that
        // clients may simply `load` all of their candidates.
        if (!file_exists($path)) {
            $this->config = [];

            return $this;
        }

        $content = file_get_contents($path);

        if (false === $content) {
            return $this;
        }

        $this->config = (array) json_decode(
            $content,
            true
        );

        return $this;
    }
}

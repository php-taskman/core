<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Config;

use Symfony\Component\Yaml\Yaml;

final class YamlRecursivePathsFinder
{
    /**
     * @var array
     */
    private $paths;

    /**
     * YamlRecursivePathsFinder constructor.
     *
     * @param array $paths
     */
    public function __construct(array $paths)
    {
        $this->paths = [];

        if (false !== $paths = array_combine($paths, $paths)) {
            $this->paths = $paths;
        }
    }

    /**
     * @return array
     */
    public function getAllPaths()
    {
        $this->findPathRecursively($this->paths);

        return $this->paths;
    }

    /**
     * @param array $paths
     */
    private function findPathRecursively(array $paths): void
    {
        foreach ($paths as $path) {
            if (!file_exists($path)) {
                continue;
            }

            $this->paths[$path] = $path;

            foreach ($this->getImports($path) as $import) {
                $resource = $import['resource'];

                if (isset($this->paths[$resource])) {
                    continue;
                }

                $this->findPathRecursively([$resource]);
            }
        }
    }

    /**
     * @param string $path
     *
     * @return mixed
     */
    private function getImports($path)
    {
        if (null === $yaml = Yaml::parseFile($path)) {
            $yaml = [];
        }

        $yaml += ['imports' => []];

        return $yaml['imports'];
    }
}

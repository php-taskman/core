<?php

declare(strict_types=1);

namespace PhpTaskman\Core\Tests\unit\Config;

use Codeception\Test\Unit;
use PhpTaskman\Core\Config\YamlRecursivePathsFinder;
use UnitTester;

/**
 * @covers \PhpTaskman\Core\Config\YamlRecursivePathsFinder
 *
 * @internal
 */
final class YamlRecursivePathsFinderTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    public function testYamlRecursivePathsFinderFeature(): void
    {
        $result = [
            'tests/unit/fixtures/Config/YamlRecursivePathsFinder/a.yml',
            'tests/unit/fixtures/Config/YamlRecursivePathsFinder/b.yml',
            'tests/unit/fixtures/Config/YamlRecursivePathsFinder/c.yml',
            'tests/unit/fixtures/Config/YamlRecursivePathsFinder/d.yml',
            'tests/unit/fixtures/Config/YamlRecursivePathsFinder/e.yml',
        ];

        $yamlRecursivePathsFinder = new YamlRecursivePathsFinder([$result[0]]);

        $this->tester->assertSame(
            array_combine($result, $result),
            $yamlRecursivePathsFinder->getAllPaths()
        );
    }
}

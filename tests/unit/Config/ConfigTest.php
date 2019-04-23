<?php

namespace PhpTaskman\Core\Tests\unit\Config;

use Codeception\Test\Unit;
use PhpTaskman\Core\Config\Config;

/**
 * @internal
 *
 * @covers \PhpTaskman\Core\Config\Config
 */
final class ConfigTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testFindFilesToIncludeInConfigurationFeature()
    {
        $files = Config::findFilesToIncludeInConfiguration(
            __DIR__ . '/../fixtures/Config/Config'
        );

        $expected = [
            '/tests/unit/fixtures/Config/Config/vendor/a/b/taskman.yml.dist',
            '/tests/unit/fixtures/Config/Config/vendor/c/d/taskman.yml',
        ];

        $dir = \realpath(__DIR__ . '/../../../');

        $expected = \array_map(
            static function ($path) use ($dir) {
                return $dir . $path;
            },
            $expected
        );

        $this->tester->assertArraySubset(\array_combine($expected, $expected), $files);
    }
}

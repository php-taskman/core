<?php

namespace PhpTaskman\Core\Tests\unit\Config\Loader;

use Codeception\Test\Unit;
use PhpTaskman\Core\Config\Loader\JsonConfigLoader;

/**
 * @internal
 *
 * @covers \PhpTaskman\Core\Config\Loader\JsonConfigLoader
 */
final class JsonConfigLoaderTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testLoadFeature()
    {
        $jsonConfigLoader = new JsonConfigLoader();

        $composerJson = __DIR__ . '/../../fixtures/Config/Loader/composer.json';

        $composerJsonArray = json_decode(
            file_get_contents($composerJson),
            true
        );
        $jsonConfigLoader->load($composerJson);

        $this::assertSame(__DIR__ . '/../../fixtures/Config/Loader/composer.json', $jsonConfigLoader->getSourceName());
        $this::assertSame($composerJsonArray, $jsonConfigLoader->export());

        $jsonConfigLoader = new JsonConfigLoader();

        $composerJson = __DIR__ . '/../../fixtures/Config/Loader/foo.json';
        $jsonConfigLoader->load($composerJson);

        $this::assertSame(__DIR__ . '/../../fixtures/Config/Loader/foo.json', $jsonConfigLoader->getSourceName());
        $this::assertSame([], $jsonConfigLoader->export());
    }
}

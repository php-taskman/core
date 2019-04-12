<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Tests\unit\Traits;

use Codeception\Test\Unit;
use PhpTaskman\Core\Traits\ConfigurationTokensTrait;

/**
 * @internal
 *
 * @covers \PhpTaskman\Core\Traits\ConfigurationTokensTrait
 */
final class ConfigurationTokensTraitTest extends Unit
{
    use ConfigurationTokensTrait;

    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testExtractProcessedTokensFeature()
    {
        $line = 'Lorem ipsum dolor sit amet';

        $filepath = __DIR__ . '/../fixtures/Traits/ConfigurationTokensTraitTest.txt';

        $tokens = $this->extractRawTokens(\file_get_contents($filepath));

        $values = \explode(' ', $line);
        $keys = \array_map(
            static function ($word) {
                return '${' . $word . '}';
            },
            $values
        );

        $this::assertSame(\array_combine($keys, $values), $tokens);
    }
}

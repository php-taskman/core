<?php

declare(strict_types=1);

namespace PhpTaskman\Core\Tests\unit;

use Codeception\Test\Unit;
use PhpTaskman\Core\Runner;
use PhpTaskman\Core\Taskman;
use Robo\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @internal
 *
 * @covers \PhpTaskman\Core\Taskman
 */
final class TaskmanTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testCreateConfiguration(): void
    {
        $configuration = Taskman::createConfiguration([]);
    }

    public function testCreateContainer(): void
    {
        $config = Taskman::createConfiguration([]);
        $classLoader = require __DIR__ . '/../_output/vendor/autoload.php';

        $container = Taskman::createContainer(
            new StringInput(''),
            new BufferedOutput(),
            new Application('test', 1),
            $config,
            $classLoader
        );
    }

    public function testCreateDefaultApplication(): void
    {
        $app = Taskman::createDefaultApplication();

        $this::assertInstanceOf(Application::class, $app);
        $this::assertFalse($app->isAutoExitEnabled());
        $this::assertSame('Taskman', $app->getName());
    }

    public function testCreateDefaultRunner(): void
    {
        $classLoader = require __DIR__ . '/../_output/vendor/autoload.php';
        $config = Taskman::createConfiguration([]);

        $container = Taskman::createContainer(
            new StringInput(''),
            new BufferedOutput(),
            new Application('test', 1),
            $config,
            $classLoader
        );

        $runner = new Runner(
            new StringInput(''),
            new BufferedOutput(),
            $classLoader
        );

        $this::assertInstanceOf(\PhpTaskman\Core\Runner::class, $runner);
        $this::assertSame($container, $runner->getContainer());
    }
}

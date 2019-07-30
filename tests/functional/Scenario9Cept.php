<?php

declare(strict_types = 1);

$I = new FunctionalTester($scenario);
$I->wantTo('A custom dynamic task can be run.');
$I->amInPath(realpath(__DIR__ . '/fixtures/test9'));

$I->runShellCommand('composer install -n --no-progress --no-scripts --no-dev --no-suggest --no-ansi');

$I->runShellCommand('../../../_output/vendor/bin/taskman');
$I->canSeeInShellOutput('--foo[=FOO]');
$I->canSeeInShellOutput('[default: "This is the global foo option value."]');

$I->runShellCommand('../../../_output/vendor/bin/taskman test:foo');
$I->canSeeInShellOutput('This is the global foo option value.');

$I->runShellCommand('../../../_output/vendor/bin/taskman test:wd');
$I->canSeeInShellOutput(realpath(getcwd()));

$I->runShellCommand('../../../_output/vendor/bin/taskman test:wd --working-dir="/"');
$I->canSeeInShellOutput(realpath('/'));

$I->expectException(
    \PHPUnit\Framework\AssertionFailedError::class,
    static function () use ($I): void {
        $I->runShellCommand('../../../_output/vendor/bin/taskman test:wd --working-dir="/foo-foo"');
    }
);

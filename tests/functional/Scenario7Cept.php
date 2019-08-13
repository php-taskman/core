<?php

declare(strict_types=1);

$I = new FunctionalTester($scenario);
$I->wantTo('A custom dynamic task can be run.');
$I->amInPath(realpath(__DIR__ . '/fixtures/test7'));

$I->runShellCommand('composer install -n --no-progress --no-scripts --no-dev --no-suggest --no-ansi');

$I->runShellCommand('../../../_output/vendor/bin/taskman --no-ansi');
$I->canSeeInShellOutput('testJ');
$I->canSeeInShellOutput('testK');

$I->runShellCommand('../../../_output/vendor/bin/taskman testJ');
$I->canSeeInShellOutput('boo boo boo');

$I->runShellCommand('../../../_output/vendor/bin/taskman testK');
$I->canSeeInShellOutput('boo boo boo');
$I->canSeeInShellOutput('baz baz baz');

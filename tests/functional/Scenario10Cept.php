<?php

declare(strict_types = 1);

$I = new FunctionalTester($scenario);
$I->wantTo('A custom dynamic task can be run.');
$I->amInPath(realpath(__DIR__ . '/fixtures/test10'));

$I->runShellCommand('composer install -n --no-progress --no-scripts --no-dev --no-suggest --no-ansi');

$I->runShellCommand('../../../_output/vendor/bin/taskman test:global-env');
$I->canSeeInShellOutput('FOO === BAR');
$I->dontSeeInShellOutput('LOCAL is a local environment variable');

$I->runShellCommand('../../../_output/vendor/bin/taskman test:local-env');
$I->canSeeInShellOutput('FOO === BAR');
$I->canSeeInShellOutput('LOCAL is a local environment variable');

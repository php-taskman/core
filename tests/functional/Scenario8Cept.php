<?php

declare(strict_types=1);

$I = new FunctionalTester($scenario);
$I->wantTo('A custom dynamic task can be run.');
$I->amInPath(realpath(__DIR__ . '/fixtures/test8'));

$I->runShellCommand('composer install -n --no-progress --no-scripts --no-dev --no-suggest --no-ansi');

$I->runShellCommand('../../../_output/vendor/bin/taskman boo');
$I->canSeeInShellOutput('');
$I->dontSeeInShellOutput('boo boo boo');

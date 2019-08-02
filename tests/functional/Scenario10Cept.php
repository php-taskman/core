<?php

declare(strict_types = 1);

$I = new FunctionalTester($scenario);
$I->wantTo('Expressions in preconditions are handled.');
$I->amInPath(realpath(__DIR__ . '/fixtures/test10'));

$I->runShellCommand('composer install -n --no-progress --no-scripts --no-dev --no-suggest --no-ansi');

$I->runShellCommand('../../../_output/vendor/bin/taskman');
$I->canSeeInShellOutput('foo command');
$I->canSeeInShellOutput('bar command');

$I->runShellCommand('../../../_output/vendor/bin/taskman foo --verbose');
$I->canSeeInShellOutput('verbose is true');

$I->runShellCommand('../../../_output/vendor/bin/taskman bar');
$I->canSeeInShellOutput('verbose is false');

<?php

declare(strict_types = 1);

$I = new FunctionalTester($scenario);
$I->wantTo('A task can run Robo tasks.');
$I->amInPath(realpath(__DIR__ . '/fixtures/test4'));

$I->runShellCommand('composer install -n --no-progress --no-scripts --no-dev --no-suggest --no-ansi');

$I->runShellCommand('../../../_output/vendor/bin/taskman --no-ansi');
$I->canSeeInShellOutput('testE');
$I->canSeeInShellOutput('testF');
$I->canSeeInShellOutput('testG');
$I->canSeeInShellOutput('testH');

$I->runShellCommand('../../../_output/vendor/bin/taskman testE');
$I->canSeeFileFound('mkdir');

$I->runShellCommand('../../../_output/vendor/bin/taskman testF');
$I->dontSeeFileFound('mkdir');

$I->runShellCommand('../../../_output/vendor/bin/taskman testG');
$I->canSeeFileFound('touch');

$I->runShellCommand('../../../_output/vendor/bin/taskman testH');
$I->dontSeeFileFound('touch');

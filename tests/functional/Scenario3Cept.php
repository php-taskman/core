<?php

declare(strict_types=1);

$I = new FunctionalTester($scenario);
$I->wantTo('Check if a complex command can be found and interpreted.');
$I->amInPath(realpath(__DIR__ . '/fixtures/test3'));

$I->runShellCommand('composer install -n --no-progress --no-scripts --no-dev --no-suggest --no-ansi');

$I->runShellCommand('../../../_output/vendor/bin/taskman --no-ansi');
$I->canSeeInShellOutput('testD');

$I->runShellCommand('../../../_output/vendor/bin/taskman testD -h --no-ansi');
$I->canSeeInShellOutput('The verbosity option. [default: "full"]');

<?php

$I = new FunctionalTester($scenario);
$I->wantTo('Check if the file taskman.yml.dist is properly taken in account.');
$I->amInPath(\realpath(__DIR__ . '/fixtures/test1'));
$I->runShellCommand('composer install --ansi -n --no-progress --no-scripts --no-dev --no-suggest');
$I->runShellCommand('../../../_output/vendor/bin/taskman');
$I->canSeeInShellOutput('testB');

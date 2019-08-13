<?php

declare(strict_types=1);

$I = new FunctionalTester($scenario);
$I->wantTo('Check if a random file is properly included from composer.json configuration.');
$I->amInPath(realpath(__DIR__ . '/fixtures/test2'));
$I->runShellCommand('composer install --ansi -n --no-progress --no-scripts --no-dev --no-suggest');
$I->runShellCommand('../../../_output/vendor/bin/taskman');
$I->canSeeInShellOutput('testC');

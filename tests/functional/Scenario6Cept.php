<?php

declare(strict_types=1);

$I = new FunctionalTester($scenario);
$I->wantTo('A task can run Robo tasks.');
$I->amInPath(realpath(__DIR__ . '/fixtures/test6'));

$I->runShellCommand('composer install -n --no-progress --no-scripts --no-dev --no-suggest --no-ansi');

$I->runShellCommand('../../../_output/vendor/bin/taskman --no-ansi');
$I->canSeeInShellOutput('testI');

$I->runShellCommand('../../../_output/vendor/bin/taskman testI');

$files = [
    'append.php' => "append.php\n// append.php",
    'prepend.php' => "// prepend.php\nprepend.php",
    'concat.php' => "// concat1\n\n// concat2\n\n",
    'concat1.php' => "// concat1\n",
    'concat2.php' => "// concat2\n",
    'write.php' => "// write.php\n",
];

foreach ($files as $file => $content) {
    $I->canSeeFileFound('../../../_output/test6/' . $file);
    $I->openFile('../../../_output/test6/' . $file);
    $I->seeInThisFile($content);
}

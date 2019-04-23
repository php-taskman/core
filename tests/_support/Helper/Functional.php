<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module;
use Codeception\TestInterface;

class Functional extends Module
{
    /**
     * @var false|string
     */
    private $directory;

    /**
     * @param \Codeception\TestInterface $test
     */
    public function _after(TestInterface $test)
    {
        parent::_after($test);

        \chdir($this->directory);
    }

    /**
     * @param \Codeception\TestInterface $test
     */
    public function _before(TestInterface $test)
    {
        parent::_before($test);
        $this->directory = \getcwd();
    }
}

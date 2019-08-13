<?php

declare(strict_types=1);

namespace PhpTaskman\Test\Plugin\Task;

use PhpTaskman\CoreTasks\Plugin\BaseTask;
use Robo\Common\BuilderAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Task\Base\Exec;

final class BooTask extends BaseTask implements BuilderAwareInterface
{
    use BuilderAwareTrait;

    const ARGUMENTS = [
        'msg',
    ];
    const NAME = 'boo';

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $arguments = $this->getTaskArguments();

        return $this
            ->collectionBuilder()
            ->addTask(
                $this->task(Exec::class, 'echo ' . $arguments['msg'])
            )
            ->run();
    }
}

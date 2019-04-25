<?php

declare(strict_types = 1);

namespace PhpTaskman\Core\Plugin\Task;

use PhpTaskman\Core\Plugin\BaseTask;
use Robo\Common\BuilderAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Task\Base\Exec;
use Robo\Task\File\loadTasks;

final class YamlTask extends BaseTask implements BuilderAwareInterface
{
    use BuilderAwareTrait;
    use loadTasks;

    /**
     * @var array
     */
    private $definition;

    /**
     * YamlTask constructor.
     *
     * @param array $definition
     */
    public function __construct(array $definition)
    {
        $this->definition = $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $tasks = $this->definition;

        if (isset($tasks['tasks'])) {
            $tasks = (array) $tasks['tasks'];
        }

        $collectionBuilder = $this->collectionBuilder();

        foreach ($tasks as $task) {
            $collectionBuilder->addTask($this->task(Exec::class, $task));
        }

        return $collectionBuilder->run();
    }
}

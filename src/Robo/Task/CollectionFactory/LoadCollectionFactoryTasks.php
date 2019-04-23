<?php

namespace PhpTaskman\Core\Robo\Task\CollectionFactory;

/**
 * Trait LoadCollectionFactoryTasks.
 */
trait LoadCollectionFactoryTasks
{
    /**
     * @return \Robo\Collection\CollectionBuilder
     */
    public function taskCollectionFactory(array $tasks, array $options = [])
    {
        return $this->task(CollectionFactory::class, $tasks, $options);
    }
}

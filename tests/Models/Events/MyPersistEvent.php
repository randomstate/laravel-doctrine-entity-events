<?php


namespace RandomState\LaravelDoctrineEntityEvents\Tests\Models\Events;


use Doctrine\ORM\Event\LifecycleEventArgs;

class MyPersistEvent
{

    public $entity;

    /**
     * @var LifecycleEventArgs
     */
    public $args;

    public function __construct($entity, LifecycleEventArgs $args)
    {
        $this->entity = $entity;
        $this->args = $args;
    }

    public function handle()
    {

    }
}
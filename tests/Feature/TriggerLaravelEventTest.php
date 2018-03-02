<?php


namespace RandomState\LaravelDoctrineEntityEvents\Tests\Feature;


use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Illuminate\Cache\CacheManager;
use Illuminate\Support\Facades\Event;
use LaravelDoctrine\ORM\DoctrineServiceProvider;
use RandomState\LaravelDoctrineEntityEvents\EventRedirector;
use RandomState\LaravelDoctrineEntityEvents\LaravelDoctrineEntityEventsServiceProvider;
use RandomState\LaravelDoctrineEntityEvents\Tests\Models\Entities\Dummy;
use RandomState\LaravelDoctrineEntityEvents\Tests\Models\Events\MyEvent;
use RandomState\LaravelDoctrineEntityEvents\Tests\Models\Events\MyPersistEvent;
use Tests\TestCase;

class TriggerLaravelEventTest extends TestCase
{

    protected function setUp()
    {
        parent::setUp();
        $this->app->register(DoctrineServiceProvider::class);
        $this->app->register(LaravelDoctrineEntityEventsServiceProvider::class);
    }

    /**
     * @return EntityManager
     */
    protected function entityManager()
    {
        return $this->app->make(EntityManager::class);
    }

    /**
     * @return EventManager
     */
    protected function eventManager()
    {
        return $this->entityManager()->getEventManager();
    }

    /**
     * @test
     */
    public function can_trigger_doctrine_event_and_see_laravel_event_fired()
    {
        Event::fake();

        /** @var EventRedirector $redirector */
        $this->app->resolving(EventRedirector::class, function(EventRedirector $redirector) {
            $redirector
                ->redirect(Dummy::class)
                ->postPersist(MyPersistEvent::class)
                ->postUpdate()
                ->default(MyEvent::class);
        });

        $args = new LifecycleEventArgs(new Dummy(), $this->entityManager());

        $this->eventManager()->dispatchEvent('postPersist', $args);
        Event::assertDispatched(MyPersistEvent::class);

        Event::fake();
        $this->eventManager()->dispatchEvent('postUpdate', $args);
        Event::assertDispatched(MyEvent::class);
    }
}
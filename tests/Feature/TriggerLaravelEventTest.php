<?php


namespace RandomState\LaravelDoctrineEntityEvents\Tests\Feature;


use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
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

    protected function setUp() : void
    {
        parent::setUp();
        $this->app->register(DoctrineServiceProvider::class);
        $this->app->register(LaravelDoctrineEntityEventsServiceProvider::class);
    }

    /**
     * @return EntityManagerInterface
     */
    protected function entityManager()
    {
        return $this->app->make(EntityManagerInterface::class);
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

    /**
     * @test
     */
    public function can_define_redirects_without_tapping_into_container()
    {
        Event::fake();

        EventRedirector::register(function(EventRedirector $redirector) {
            $redirector
                ->redirect(Dummy::class)
                ->postPersist(MyPersistEvent::class)
                ->postUpdate()
                ->default(MyEvent::class);
        });

        $args = new LifecycleEventArgs(new Dummy(), $this->entityManager());

        $this->eventManager()->dispatchEvent('postPersist', $args);
        Event::assertDispatched(MyPersistEvent::class, function(MyPersistEvent $event) {
            return $event->entity instanceof Dummy;
        });
    }
}
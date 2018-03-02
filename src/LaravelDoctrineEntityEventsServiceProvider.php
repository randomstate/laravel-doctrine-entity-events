<?php


namespace RandomState\LaravelDoctrineEntityEvents;


use Doctrine\ORM\EntityManager;
use Illuminate\Support\ServiceProvider;

class LaravelDoctrineEntityEventsServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->resolving(EventRedirector::class, function(EventRedirector $redirector) {
           if($onBoot = EventRedirector::$onBoot) {
               $onBoot($redirector);
           }

           return $redirector;
        });

        $this->app->resolving(EntityManager::class, function(EntityManager $entityManager) {
           $entityManager->getEventManager()->addEventSubscriber($this->app->make(EventRedirector::class));
           return $entityManager;
        });
    }
}
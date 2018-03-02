# Laravel-Doctrine Entity Events
This package provides a simple way to hook into Doctrine2 Entity events and remap them to
native Laravel events.

## Getting Started

* Install using `composer require randomstate/laravel-doctrine-entity-events`
* Add `RandomState\LaravelDoctrineEntityEvents\LaravelDoctrineEntityEventsServiceProvider::class`
 to the `providers` section of `config/app.php`

## Usage

### Configuration

**Helper Method** _(recommended)_

```php
<?php

use RandomState\LaravelDoctrineEntityEvents\EventRedirector;

public class AppServiceProvider extends ServiceProvider {

    public function register() {
        EventRedirector::register(function(EventRedirector $redirector) {
           $redirector->redirect(MyEntity::class)
            ->postPersist(MyEntityWasCreated::class)
            ->postUpdate(MyEntityWasUpdated::class)
            ->postFlush() // falls back to default as no destination is provided
            ->default(SomethingHappenedToMyEntityEvent::class); 
        });
    }
}
```

**Intercept Service Instantiation**

```php
<?php

use RandomState\LaravelDoctrineEntityEvents\EventRedirector;

public class AppServiceProvider extends ServiceProvider {

    public function register() {
        $this->app->resolving(EventRedirector::class, (function(EventRedirector $redirector) {
           $redirector->redirect(MyEntity::class)
            ->postPersist(MyEntityWasCreated::class)
            ->postUpdate(MyEntityWasUpdated::class)
            ->postFlush() // falls back to default as no destination is provided
            ->default(SomethingHappenedToMyEntityEvent::class); 
        }));
    }
}
```

### Events

Every Laravel Event you specify as a destination will be supplied the entity and doctrine event arguments on creation.
The entity is supplied as the first argument so you can conveniently ignore other event arguments when you are only interested
in the entity itself.

```php
<?php

class MyEntityWasCreated {
    
    public function __construct(MyEntity $entity, LifecycleEventArgs $eventArgs) {
        // do something
    }
    
    public function handle() {
        // do something
    }
    
}
```

## Advanced Usage

If you need to customise the way the event is instantiated, supply a closure as your 'destination'
when defining the redirects.

```php
<?php

EventRedirector::register(function(EventRedirector $redirector) {
           $redirector->redirect(MyEntity::class)
            ->postPersist(MyEntityWasCreated::class)
            ->postUpdate(function(MyEntity $entity) {
                $mailer = app('mailer');
                event(new MyEntityWasUpdated($entity, $mailer)); // customised instantiation
            })
            ->postFlush() // falls back to default as no destination is provided
            ->default(SomethingHappenedToMyEntityEvent::class); 
        });
```
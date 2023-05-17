<?php


namespace RandomState\LaravelDoctrineEntityEvents;


use App\Identity\Entities\Organisation;
use Closure;
use Doctrine\Common\EventSubscriber;

class EventRedirector implements EventSubscriber
{
    public static $onBoot;

    public static function register(Closure $redirects) {
        static::$onBoot = $redirects;
    }

    /**
     * @var Redirect[][]
     */
    protected $redirects = [];

    /**
     * @param string $entityClass
     *
     * @return Redirect
     */
    public function redirect($entityClass)
    {
        return $this->registerRedirect($entityClass, new Redirect);
    }

    public function __call($event, array $arguments)
    {
        if(! ($eventArgs = $arguments[0] ?? false)) {
            return;
        }

        $entity = $eventArgs->getObject();
        $redirects = $this->getRedirectsForClass(get_class($entity)); //$this->redirects[get_class($entity)] ?? false;

        if($redirects) {
            foreach($redirects as $redirect) {
                array_unshift($arguments, $entity);
                $redirect->handle($event, $arguments);
            }
        }
    }

    public function getRedirectsForClass($class)
    {
        $redirects = $this->redirects[$class] ?? [];

        $parent = get_parent_class($class);

        if(!$parent) {
            return $redirects;
        }

        return array_merge($redirects, $this->getRedirectsForClass($parent));
    }

    /**
     * @param $entityClass
     * @param Redirect $redirect
     *
     * @return Redirect
     */
    protected function registerRedirect($entityClass, Redirect $redirect)
    {
        if(!($this->redirects[$entityClass] ?? false)) {
            $this->redirects[$entityClass] = [];
        }

        $this->redirects[$entityClass][] = $redirect;

        return $redirect;
    }

    public function getSubscribedEvents()
    {
        $events = [];

        foreach($this->redirects as $entity => $redirects) {
            foreach($redirects as $redirect) {
                $events = array_merge($events, $redirect->getSubscribedEvents());
            }
        }

        return $events;
    }

}
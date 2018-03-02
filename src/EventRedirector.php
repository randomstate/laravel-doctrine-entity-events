<?php


namespace RandomState\LaravelDoctrineEntityEvents;


use Doctrine\Common\EventSubscriber;

class EventRedirector implements EventSubscriber
{

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
        return $this->register($entityClass, new Redirect);
    }

    public function __call($event, array $arguments)
    {
        if(! ($eventArgs = $arguments[0] ?? false)) {
            return;
        }

        $entity = get_class($eventArgs->getObject());
        $redirects = $this->redirects[$entity] ?? false;

        if($redirects) {
            foreach($redirects as $redirect) {
                $redirect->handle($event, $arguments);
            }
        }
    }

    /**
     * @param $entityClass
     * @param Redirect $redirect
     *
     * @return Redirect
     */
    protected function register($entityClass, Redirect $redirect)
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
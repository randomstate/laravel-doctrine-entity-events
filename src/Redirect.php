<?php


namespace RandomState\LaravelDoctrineEntityEvents;


class Redirect
{

    protected $subscribedEvents = [];
    protected $handlers = [];

    /**
     * @var \Closure
     */
    protected $default;

    /**
     * @param $methodName
     * @param array $arguments
     *
     * @return $this
     */
    public function __call($methodName, array $arguments)
    {
        return $this->custom($methodName, $arguments[0] ?? null);
    }

    /**
     * @param $event
     * @param string | \Closure | null $destination
     *
     * @return $this
     */
    public function custom($event, $destination = null)
    {
        $this->subscribedEvents[$event] = $event;

        if(!($this->handlers[$event] ?? false)){
            $this->handlers[$event] = [];
        }

        $this->handlers[$event][] = $this->generateHandler($destination);
        return $this;
    }

    public function default($destination = null)
    {
        $this->default = $this->generateHandler($destination);
        return $this;
    }

    /**
     * @param $event
     * @param array $args
     */
    public function handle($event, array $args)
    {
        foreach($this->getHandlers($event) as $handler) {
            $handler($args);
        }
    }

    public function getHandlers($event)
    {
        return $this->handlers[$event] ?? $this->default ?? function() {};
    }

    /**
     * @param $destination
     *
     * @return callable|\Closure
     */
    protected function generateHandler($destination)
    {
        if (is_string($destination)) {
            $handler = function ($args) use ($destination) {
                event(new $destination(...$args));
            };
        } elseif (is_callable($destination)) {
            $handler = $destination;
        } else {
            $handler = function (...$args) {
                $fallback = $this->default ?? function () {};
                $fallback(...$args);
            };
        }

        return $handler;
    }

    public function getSubscribedEvents()
    {
        return array_values($this->subscribedEvents);
    }
}
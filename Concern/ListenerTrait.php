<?php


namespace Plugins\Concern;


use OpenTracing\Span;
use Swoft\Event\EventInterface;

trait ListenerTrait
{
    /**
     * @var Span
     */
    private $span;

    public function _before(EventInterface $event, string $name, \Closure $closure = null)
    {
        $serverSpan = $this->tracerManager->getServerSpan();
        if (empty($serverSpan)) {
            return;
        }
        $args = $event->getParams();
        $target = $event->getTarget();
        $this->span = $this->startSpan($name);
        $this->span->setTag('method', $event->getName());
        $this->span->setTag('who', is_object($target) ? get_class($target) : (string)$target);
        $this->span->setTag('args', is_array($args) ? json_encode($args ?: []) : (string)$args);
        if (is_callable($closure)) {
            call_user_func($closure);
        }
    }

    public function _after(EventInterface $event, \Closure $closure = null)
    {
        $serverSpan = $this->tracerManager->getServerSpan();
        if (empty($serverSpan)) {
            return;
        }
        $this->span->log($event->getParams());
        if (is_callable($closure)) {
            call_user_func($closure);
        }
        $this->span->finish();
    }
}

<?php


namespace Plugins\Concern;


use Plugins\Manager\TracerManager;
use OpenTracing\GlobalTracer;
use OpenTracing\Span;
use const OpenTracing\Formats\TEXT_MAP;
use const OpenTracing\Tags\SPAN_KIND;
use const OpenTracing\Tags\SPAN_KIND_RPC_SERVER;

/**
 * Trait SpanStarterTrait
 * @package App\Plugins\Concern
 * @property TracerManager $tracerManager
 */
trait SpanStarterTrait
{

    private static $cache_key = 'tracer.root';

    protected function startSpan(
        string $name,
        array $options = [],
        string $kind = SPAN_KIND_RPC_SERVER
    ): Span {
        $root = context()->get(self::$cache_key);
        if (! ($root instanceof Span)){
            $request = context()->getRequest();
            $carrier = $request->getHeaders();
            $spanContext = GlobalTracer::get()->extract(TEXT_MAP, $carrier);
            if ($spanContext){
                $options['child_of'] = $spanContext;
            }
            $root = GlobalTracer::get()->startSpan($name, $options);
            $root->setTag(SPAN_KIND, $kind);
            context()->set(self::$cache_key, $root);
            $this->tracerManager->setServerSpan($root);
            return $root;
        }
        $option['child_of'] = $root->getContext();
        $child = GlobalTracer::get()->startSpan($name, $option);
        $child->setTag(SPAN_KIND, $kind);
        return $child;
    }
}

<?php


namespace App\Plugins\Middleware;



use Swoft\Co;
use OpenTracing\Span;
use App\Plugins\Manager\TracerManager;
use Psr\Http\Message\ResponseInterface;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use App\Plugins\Concern\SpanStarterTrait;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoft\Http\Server\Contract\MiddlewareInterface;

/**
 * Class JaegerMiddleware
 * @package App\Plugins\Middleware
 * @Bean()
 */
class JaegerMiddleware implements MiddlewareInterface
{
    use SpanStarterTrait;

    /**
     * @Inject()
     * @var TracerManager
     */
    public $tracerManager;

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (false == env('JAEGER_OPEN', false)){
            return $handler->handle($request);
        }
        $this->buildSpan($request);
        $response = $handler->handle($request);
        $this->tracerManager->flush();
        return $response;
    }

    protected function buildSpan(ServerRequestInterface $request): Span
    {
        $uri = $request->getUri();
        $span = $this->startSpan('request');
        $span->setTag('coroutine.id', (string) Co::id());
        $span->setTag('request.path', (string) $uri);
        $span->setTag('request.method', $request->getMethod());
        foreach ($request->getHeaders() as $key => $value) {
            $span->setTag('request.header.' . $key, implode(', ', $value));
        }
        return $span;
    }
}

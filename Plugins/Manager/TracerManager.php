<?php


namespace App\Plugins\Manager;


use OpenTracing\Span;
use Swoft\Co;
use Jaeger\Config;
use OpenTracing\GlobalTracer;
use App\Plugins\Sampler\SwooleProbabilisticSampler;
use  App\Plugins\Transport\JaegerTransportLog;
use  App\Plugins\Transport\JaegerTransportUdp;
use Swoft\Bean\Annotation\Mapping\Bean;
use const OpenTracing\Formats\TEXT_MAP;

/**
 * @Bean()
 * Class TracerManager
 * @package App\Plugins\Manager
 */
class TracerManager
{

    protected $serverSpans = [];

    protected $configs;

    /**
     * @throws \Exception
     */
    public function init()
    {
        $config = Config::getInstance();
        $config->setSampler(new SwooleProbabilisticSampler($this->getIp(), env('JAEGER_HTTP_PORT', 80), env('JAEGER_RATE')));

        $mode = env('JAEGER_MODE', 1);
        if ($mode == 1) {
            $config->setTransport(new JaegerTransportUdp(env('JAEGER_SERVER_HOST'), 8000));
        } elseif ($mode == 2) {
            $config->setTransport(new JaegerTransportLog(4000));
        } else {
            throw new \Exception("jaeger's mode is not set");
        }
        try {
            $tracer = $config->initTracer(env('JAEGER_PNAME'));
        } catch (\Exception $e) {
            throw $e;
        }

        GlobalTracer::set($tracer); // optional
        $this->configs = $config;
    }

    public function setServerSpan(Span $span)
    {
        $cid = Co::tid();
        $this->serverSpans[$cid] = $span;
    }

    /**
     * @return Span|null
     */
    public function getServerSpan()
    {
        $cid = Co::tid();
        return $this->serverSpans[$cid] ?? null;
    }


    public function getHeader()
    {
        $headers = [];
        $cid = Co::tid();
        if (isset($this->serverSpans[$cid])) {
            GlobalTracer::get()->inject(
                $this->serverSpans[$cid]->getContext(),
                TEXT_MAP,
                $headers);

            return $headers;
        } else {
            return [];
        }

    }


    public function flush()
    {
        $config = $this->configs;
        $cid = Co::tid();
        $config->flush();
        $this->getServerSpan()->finish();
        unset($this->serverSpans[$cid]);
    }

    private function getIp()
    {
//        $result = shell_exec("/sbin/ifconfig");
//        if (preg_match_all("/inet (\d+\.\d+\.\d+\.\d+)/", $result, $match) !== 0)  // 这里根据你机器的具体情况， 可能要对“inet ”进行调整， 如“addr:”，看如下注释掉的if
//        {
//            foreach ($match [0] as $k => $v) {
//                if ($match [1] [$k] != "127.0.0.1") {
//                    return $match[1][$k];
//                }
//            }
//        }
        return '127.0.0.1';
    }
}

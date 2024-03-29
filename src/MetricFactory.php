<?php

namespace Metric;

use Metric\Contract\MetricFactoryInterface;
use Metric\Exception\InvalidArgumentException;
use Metric\Storage\Redis;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\APC;
use Prometheus\Storage\InMemory;

class MetricFactory
{
    /**
     * @var array
     */
    private static $config = [];

    /**
     * @param array $config
     * @return MetricFactoryInterface
     */
    public static function getFactory(array $config = []): MetricFactoryInterface
    {
        self::$config = !empty($config) ? $config : require __DIR__ . '/../publish/metric.php';
        $adapter = self::$config['default'] ?? 'prometheus';
        $cfg = self::$config['metric'][$adapter];

        switch ($adapter) {
            case 'prometheus':
                $storageCfg = $cfg['storage_adapter'];
                switch ($storageCfg) {
                    case 'redis':
                        $redisCfg = self::$config['redis'];
                        Redis::setDefaultOptions($redisCfg);
                        // 设置存到redis里的key的前缀
                        if (isset($cfg['redis_prefix']) && !empty($cfg['redis_prefix'])) {
                            Redis::setPrefix($cfg['redis_prefix']);
                        }
                        $storageAdapter = new Redis();
                        break;
                    case 'apc':
                        $storageAdapter = new APC();
                        break;
                    case 'in-memory':
                        $storageAdapter = new InMemory();
                        break;
                    default:
                        throw new InvalidArgumentException('invalid storage adapter');
                }
                $registry = new CollectorRegistry($storageAdapter);
                $metric = new \Metric\Adapter\Prometheus\MetricFactory($registry, self::$config);
                break;
            default:
                throw new InvalidArgumentException('invalid metric adapter');
        }
        return $metric;
    }
}
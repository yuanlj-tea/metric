### Installation

Use composer:

```php
composer require yuanlj-tea/metric
```

### Usage

配置文件

```php+HTML
配置文件模板路径，复制一份到自己的项目：
/publish/metric.php

配置文件内容：
		'default' => 'prometheus',  // 配置使用的适配器
    'default_metric_interval' => 5,
    'metric' => [
        'prometheus' => [
            'driver' => \Metric\Adapter\Prometheus\MetricFactory::class,		// prometheus使用的class
            'mode' => \Metric\Adapter\Prometheus\Constants::SCRAPE_MODE,		// 模式，抓取模式或主动推送模式
            'storage_adapter' => 'redis',		// 数据存储方式
            'namespace' => 'app-name',		// config namespace
            'scrape_host' => '0.0.0.0',		// 抓取模式时的host
            'scrape_port' => '9502',		// 抓取模式时的port
            'scrape_path' => '/metrics',		// 抓取模式时的请求接口
            'push_host' => '0.0.0.0',		// 主动推送模式的host
            'push_port' => '9091',		// 主动推送模式的port
            'push_interval' => 5,		// 主动推送模式的间隔时间
        ],
    ],
    'redis' => [		// 使用redis存储时的redis相关配置
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => 123456,
        'timeout' => 1, // in seconds
        'read_timeout' => '10', // in seconds
        'persistent_connections' => false
    ],
```

useage demo

```php
<?php

namespace Metric;

use Metric\Contract\MetricFactoryInterface;

class Metric
{
    /**
     * @var MetricFactoryInterface
     */
    private static $metric;

    /**
     * @param array $config
     * @return Metric
     */
    public static function getMetric(array $config = []): self
    {
        self::$metric = MetricFactory::getFactory($config);
        return new self();
    }

    public function count(string $name, ?int $delta = 1, ?array $labels = [])
    {
        self::$metric->makeCounter($name, array_keys($labels))
            ->with(...array_values($labels))
            ->add($delta);
    }

    public function gauge(string $name, float $value, ?array $labels = [])
    {
        self::$metric->makeGauge($name, array_keys($labels))
            ->with(...array_values($labels))
            ->set($value);
    }

    public function put(string $name, float $sample, ?array $labels = [])
    {
        self::$metric->makeHistogram($name, array_keys($labels))
            ->with(...array_values($labels))
            ->put($sample);
    }

    public function time(string $name, callable $func, ?array $args = [], ?array $labels = [])
    {
        $timer = new Timer(self::$metric, $name, $labels);
        return $func(...$args);
    }

    public function show()
    {
        self::$metric->handle();
    }
}
```




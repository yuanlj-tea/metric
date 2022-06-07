<?php

return [
    'default' => 'prometheus',
    'default_metric_interval' => 5,
    'metric' => [
        'prometheus' => [
            'driver' => \Metric\Adapter\Prometheus\MetricFactory::class,
            'mode' => \Metric\Adapter\Prometheus\Constants::SCRAPE_MODE,
            'storage_adapter' => 'redis',
            'redis_prefix' => '', // 存到redis里的key的前缀
            'namespace' => 'app-name',
            'scrape_host' => '0.0.0.0',
            'scrape_port' => '9502',
            'scrape_path' => '/metrics',
            'push_host' => '0.0.0.0',
            'push_port' => '9091',
            'push_interval' => 5,
        ],
    ],
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => 123456,
        'timeout' => 1, // in seconds
        'read_timeout' => '10', // in seconds
        'persistent_connections' => false
    ],
];

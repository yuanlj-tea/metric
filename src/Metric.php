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

    public function put(string $name, float $sample, ?array $labels = [], $buckets = null)
    {
        self::$metric->makeHistogram($name, array_keys($labels), $buckets)
            ->with(...array_values($labels))
            ->put($sample);
    }

    public function time(string $name, callable $func, ?array $args = [], ?array $labels = [], $time = null)
    {
        $timer = new Timer(self::$metric, $name, $labels, $time);
        return $func(...$args);
    }

    public function show(int $returnHeader = 0)
    {
        self::$metric->handle($returnHeader);
    }
}
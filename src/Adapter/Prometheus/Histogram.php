<?php

namespace Metric\Adapter\Prometheus;

use Metric\Contract\HistogramInterface;
use Prometheus\CollectorRegistry;

class Histogram implements HistogramInterface
{
    /**
     * @var CollectorRegistry
     */
    protected $registry;

    /**
     * @var \Prometheus\Histogram
     */
    protected $histogram;

    /**
     * @var string[]
     */
    protected $labelValues = [];

    public function __construct(CollectorRegistry $registry, string $namespace, string $name, string $help, array $labels)
    {
        $this->registry = $registry;
        $this->histogram = $registry->getOrRegisterHistogram($namespace, $name, $help, $labels);
    }


    public function with(string ...$labelValues): HistogramInterface
    {
        $this->labelValues = $labelValues;
        return $this;
    }

    public function put(float $sample): void
    {
        $this->histogram->observe($sample, $this->labelValues);
    }


}
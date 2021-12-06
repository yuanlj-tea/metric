<?php

namespace Metric\Adapter\Prometheus;

use Metric\Contract\GaugeInterface;
use Prometheus\CollectorRegistry;

class Gauge implements GaugeInterface
{
    /**
     * @var \Prometheus\CollectorRegistry
     */
    protected $registry;

    /**
     * @var \Prometheus\Gauge
     */
    protected $gauge;

    /**
     * @var string[]
     */
    protected $labelValues = [];

    public function __construct(CollectorRegistry $registry, string $namespace, string $name, string $help, array $labels)
    {
        $this->registry = $registry;
        $this->gauge = $registry->getOrRegisterGauge($namespace, $name, $help, $labels);
    }

    public function with(string ...$labelValues): GaugeInterface
    {
        $this->labelValues = $labelValues;
        return $this;
    }

    public function set(float $value): void
    {
        $this->gauge->set($value, $this->labelValues);
    }

    public function add(float $delta): void
    {
        $this->gauge->incBy($delta, $this->labelValues);
    }
}
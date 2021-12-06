<?php

namespace Metric\Adapter\Prometheus;

use Metric\Contract\CounterInterface;
use Prometheus\CollectorRegistry;

class Counter implements CounterInterface
{
    /**
     * @var \Prometheus\CollectorRegistry
     */
    protected $registry;

    /**
     * @var \Prometheus\Counter
     */
    protected $counter;

    /**
     * @var string[]
     */
    protected $labelValues = [];

    public function __construct(CollectorRegistry $registry, string $namespace, string $name, string $help, array $labels)
    {
        $this->registry = $registry;
        $this->counter = $registry->getOrRegisterCounter($namespace, $name, $help, $labels);
    }

    public function with(string ...$labelValues): CounterInterface
    {
        $this->labelValues = $labelValues;
        return $this;
    }

    public function add(int $delta): void
    {
        $this->counter->incBy($delta, $this->labelValues);
    }

}
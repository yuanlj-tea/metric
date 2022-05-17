<?php

namespace Metric;

use Metric\Contract\MetricFactoryInterface;

class Timer
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array<string,string>
     */
    protected $labels;

    /**
     * @var float
     */
    protected $time;

    /**
     * @var bool
     */
    protected $ended = false;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var MetricFactoryInterface
     */
    protected $metric;

    public function __construct(MetricFactoryInterface $metricFactory, string $name = '', ?array $default = [], $time = null)
    {
        $this->metric = $metricFactory;
        $this->name = $name;
        $this->labels = $default;
        $this->time = !empty($time) ? $time : microtime(true);
    }

    public function __destruct()
    {
        $this->end();
    }

    /**
     * @param array|null $labels
     */
    public function end(?array $labels = [], $bucket = null): void
    {
        if ($this->ended) {
            return;
        }

        foreach ($labels as $k => $v) {
            if (array_key_exists($k, $this->labels)) {
                $this->labels[$k] = $v;
            }
        }

        $historm = $this->metric
            ->makeHistogram($this->name, array_keys($this->labels), $bucket)
            ->with(...array_values($this->labels));
        $d = (float)microtime(true) - $this->time;
        if ($d < 0) {
            $d = (float)0;
        }
        $historm->put($d);
        $this->ended = true;
    }
}
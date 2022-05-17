<?php

namespace Metric\Contract;

interface MetricFactoryInterface
{
    /**
     * create a counter
     * @param string $name name of metric
     * @param array|null $labelNames label of metric
     * @return CounterInterface
     */
    public function makeCounter(string $name, ?array $labelNames = []): CounterInterface;

    /**
     * create a gauge
     * @param string $name name of metric
     * @param array|null $labelNames label of metric
     * @return GaugeInterface
     */
    public function makeGauge(string $name, ?array $labelNames = []): GaugeInterface;

    /**
     * create a Histogram
     * @param string $name name of metric
     * @param array|null $labelNames label of metric
     * @return HistogramInterface
     */
    public function makeHistogram(string $name, ?array $labelNames = [], $buckets = null): HistogramInterface;

    /**
     * handle the metric collecting/reporting/serving tasks
     */
    public function handle(int $returnHeader = 0): void;
}
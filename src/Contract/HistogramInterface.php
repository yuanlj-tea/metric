<?php

namespace Metric\Contract;

interface HistogramInterface
{
    public function with(string ...$labelValues): self;

    public function put(float $sample): void;
}
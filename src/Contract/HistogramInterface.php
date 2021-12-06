<?php

namespace Metric\Contract;

interface HistogramInterface
{
    public function with(float ...$labelValues): self;

    public function put(float $sample): void;
}
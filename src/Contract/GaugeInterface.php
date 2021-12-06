<?php

namespace Metric\Contract;

interface GaugeInterface
{
    public function with(string ...$labelValues): self;

    public function set(float $value): void;

    public function add(float $delta): void;
}
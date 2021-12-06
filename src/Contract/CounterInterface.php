<?php

namespace Metric\Contract;

interface CounterInterface
{
    public function with(string ...$labelValues): self;

    public function add(int $delta): void;
}
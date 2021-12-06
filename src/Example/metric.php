<?php
require_once __DIR__ . '/../../vendor/autoload.php';

function test()
{
    \Metric\Metric::getMetric()->put('test', 1, ['foo' => 1, 'bar' => 2]);
}

test();
<?php

declare(strict_types=1);

namespace WyriHaximus\Metrics;

use WyriHaximus\Metrics\Histogram\Buckets;
use WyriHaximus\Metrics\Label\Name;
use WyriHaximus\Metrics\Registry\Counters;
use WyriHaximus\Metrics\Registry\Gauges;
use WyriHaximus\Metrics\Registry\Histograms;

interface Registry
{
    public function counter(string $name, string $description, Name ...$requiredLabelNames): Counters;

    public function gauge(string $name, string $description, Name ...$requiredLabelNames): Gauges;

    public function histogram(string $name, string $description, Buckets $buckets, Name ...$requiredLabelNames): Histograms;

    public function print(Printer $printer): string;
}

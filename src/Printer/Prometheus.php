<?php

declare(strict_types=1);

namespace WyriHaximus\Metrics\Printer;

use WyriHaximus\Metrics\Label;
use WyriHaximus\Metrics\Printer;
use WyriHaximus\Metrics\Registry\Counters;
use WyriHaximus\Metrics\Registry\Gauges;
use WyriHaximus\Metrics\Registry\Histograms;

use function array_map;
use function count;
use function implode;
use function strlen;

use const WyriHaximus\Constants\Numeric\ZERO;

final class Prometheus implements Printer
{
    private const NL = "\n";

    public function counter(Counters $counters): string
    {
        $string = '';
        foreach ($counters->counters() as $counter) {
            $string    .= $counters->name() . '_total';
            $labels     = $counter->labels();
            $labelCount = count($labels);
            if ($labelCount !== ZERO) {
                $string .= '{';
                $string .= implode(',', array_map(static fn (Label $label) => $label->name() . '="' . $label->value() . '"', $labels));
                $string .= '}';
            }

            $string .= ' ' . $counter->count() . self::NL;
        }

        if ($string !== '') {
            $head = '';
            if (strlen($counters->description()) > 0) {
                $head = '# HELP ' . $counters->name() . '_total ' . $counters->description() . self::NL;
            }

            $head .= '# TYPE ' . $counters->name() . '_total counter' . self::NL;

            $string = $head . $string;
        }

        return $string . self::NL;
    }

    public function gauge(Gauges $gauges): string
    {
        $string = '';

        foreach ($gauges->gauges() as $gauge) {
            $string    .= $gauge->name();
            $labels     = $gauge->labels();
            $labelCount = count($labels);
            if ($labelCount !== ZERO) {
                $string .= '{';
                $string .= implode(',', array_map(static fn (Label $label) => $label->name() . '="' . $label->value() . '"', $labels));
                $string .= '}';
            }

            $string .= ' ' . $gauge->gauge() . self::NL;
        }

        if ($string !== '') {
            $head = '';

            if (strlen($gauges->description()) > 0) {
                $head = '# HELP ' . $gauges->name() . ' ' . $gauges->description() . self::NL;
            }

            $head .= '# TYPE ' . $gauges->name() . ' gauge' . self::NL;

            $string = $head . $string;
        }

        return $string . self::NL;
    }

    public function histogram(Histograms $histograms): string
    {
        $string = '';

        foreach ($histograms->histograms() as $histogram) {
            $labels       = $histogram->labels();
            $labelCount   = count($labels);
            $labelsString = '';
            if ($labelCount !== ZERO) {
                $labelsString = implode(',', array_map(static fn (Label $label) => $label->name() . '="' . $label->value() . '"', $labels));
            }

            foreach ($histogram->buckets() as $bucket) {
                $string .= $histogram->name() . '_bucket{le="' . $bucket->le() . '"';
                if ($labelCount !== ZERO) {
                    $string .= ',' . $labelsString;
                }

                $string .= '} ' . $bucket->count() . self::NL;
            }

            $string .= $histogram->name() . '_sum';
            if ($labelCount !== ZERO) {
                $string .= '{' . $labelsString . '}';
            }

            $string .= ' ' . $histogram->summary() . self::NL;

            $string .= $histogram->name() . '_count';
            if ($labelCount !== ZERO) {
                $string .= '{' . $labelsString . '}';
            }

            $string .= ' ' . $histogram->count() . self::NL;
        }

        if ($string !== '') {
            $head = '';

            if (strlen($histograms->description()) > 0) {
                $head = '# HELP ' . $histograms->name() . ' ' . $histograms->description() . self::NL;
            }

            $head .= '# TYPE ' . $histograms->name() . ' histogram' . self::NL;

            $string = $head . $string;
        }

        return $string . self::NL;
    }
}

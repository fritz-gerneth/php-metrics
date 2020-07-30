<?php

declare(strict_types=1);

namespace WyriHaximus\Tests\Metrics;

use PHPUnit\Framework\TestCase;
use WyriHaximus\Metrics\Factory;
use WyriHaximus\Metrics\Gauge;
use WyriHaximus\Metrics\Label;
use WyriHaximus\Metrics\Label\Name;

use function array_map;
use function iterator_to_array;

final class RegistryGaugeTest extends TestCase
{
    /**
     * @test
     */
    public function counter(): void
    {
        $metricName        = 'name';
        $metricDescription = 'Description';

        $registry = Factory::create();
        $gauge    = $registry->gauge($metricName, $metricDescription, new Name('label'), new Name('node'));
        $gauge->gauge(new Label('node', 'mushroom'), new Label('label', 'label'))->set(133);
        $gauge->gauge(new Label('node', 'mushroom'), new Label('label', 'label'))->set(133);
        $gauge->gauge(new Label('node', 'mushroom'), new Label('label', 'labol'))->incr();
        $counters = iterator_to_array($gauge->gauges());
        self::assertCount(2, $counters);
        self::assertSame([133, 1], array_map(static fn (Gauge $gauge) => $gauge->gauge(), $counters));
        self::assertSame([['label', 'mushroom'], ['labol', 'mushroom']], array_map(static fn (Gauge $gauge) => array_map(static fn (Label $label) => $label->value(), $gauge->labels()), $counters));

        $gauge->gauge(new Label('node', 'mushroom'), new Label('label', 'label'))->dcr(63);
        $gauge->gauge(new Label('node', 'mushroom'), new Label('label', 'labol'))->incrBy(63);
        $counters = iterator_to_array($gauge->gauges());
        self::assertCount(2, $counters);
        self::assertSame([132, 64], array_map(static fn (Gauge $gauge) => $gauge->gauge(), $counters));
        self::assertSame([['label', 'mushroom'], ['labol', 'mushroom']], array_map(static fn (Gauge $gauge) => array_map(static fn (Label $label) => $label->value(), $gauge->labels()), $counters));

        $gauge->gauge(new Label('node', 'mushroom'), new Label('label', 'labol'))->dcrBy(33);
        $gauge->gauge(new Label('node', 'mushroom'), new Label('label', 'labal'));
        $counters = iterator_to_array($gauge->gauges());
        self::assertCount(3, $counters);
        self::assertSame([132, 31, 0], array_map(static fn (Gauge $gauge) => $gauge->gauge(), $counters));
        self::assertSame([['label', 'mushroom'], ['labol', 'mushroom'], ['labal', 'mushroom']], array_map(static fn (Gauge $gauge) => array_map(static fn (Label $label) => $label->value(), $gauge->labels()), $counters));

        self::assertSame($metricName, $gauge->name());
        self::assertSame($metricDescription, $gauge->description());
        foreach ($counters as $uniqueCounter) {
            self::assertSame($metricName, $uniqueCounter->name());
            self::assertSame($metricDescription, $uniqueCounter->description());
        }
    }

    /**
     * @test
     */
    public function faultyLabels(): void
    {
        self::expectException(Label\GivenLabelsDontMatchExpectedLabels::class);

        $metricName        = 'name';
        $metricDescription = 'Description';

        $registry = Factory::create();
        $registry->gauge($metricName, $metricDescription, new Name('label'))->gauge(new Label('labiel', 'boem'));
    }
}

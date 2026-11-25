<?php

namespace ItmoSoft\Tax\Tests\Unit;

use InvalidArgumentException;
use ItmoSoft\Tax\Services\TaxCalculator;
use ItmoSoft\Tax\Tests\TestCase;

class TaxCalculatorTest extends TestCase
{
    public function test_exclusive_tax_is_added(): void
    {
        $result = app(TaxCalculator::class)->calculate(100, [
            ['name' => 'VAT', 'rate' => 10, 'is_compound' => false],
        ]);

        $this->assertSame(100.0, $result['net']);
        $this->assertSame(10.0, $result['tax']);
        $this->assertSame(110.0, $result['gross']);
    }

    public function test_inclusive_tax_is_extracted(): void
    {
        $result = app(TaxCalculator::class)->calculate(110, [
            ['rate' => 10, 'is_compound' => false],
        ], true);

        $this->assertSame(100.0, $result['net']);
        $this->assertSame(10.0, $result['tax']);
    }

    public function test_compound_tax_is_sequential(): void
    {
        $result = app(TaxCalculator::class)->calculate(100, [
            ['rate' => 10, 'is_compound' => false],
            ['rate' => 5, 'is_compound' => true],
        ]);

        $this->assertSame(15.5, $result['tax']);
    }

    public function test_negative_amount_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(TaxCalculator::class)->calculate(-1, []);
    }

    public function test_non_finite_rate_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(TaxCalculator::class)->calculate(100, [
            ['rate' => INF, 'is_compound' => false],
        ]);
    }

    public function test_negative_rounding_scale_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(TaxCalculator::class)->calculate(100, [], scale: -1);
    }
}

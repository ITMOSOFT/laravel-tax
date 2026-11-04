<?php
namespace ItmoSoft\Tax\Tests\Unit;
use ItmoSoft\Tax\Services\TaxCalculator;
use ItmoSoft\Tax\Tests\TestCase;
class TaxCalculatorTest extends TestCase
{
    public function test_exclusive_tax_is_added(): void { $r = app(TaxCalculator::class)->calculate(100, [['name'=>'VAT','rate'=>10,'is_compound'=>false]]); $this->assertSame(100.0,$r['net']); $this->assertSame(10.0,$r['tax']); $this->assertSame(110.0,$r['gross']); }
    public function test_inclusive_tax_is_extracted(): void { $r = app(TaxCalculator::class)->calculate(110, [['rate'=>10,'is_compound'=>false]], true); $this->assertSame(100.0,$r['net']); $this->assertSame(10.0,$r['tax']); }
    public function test_compound_tax_is_sequential(): void { $r = app(TaxCalculator::class)->calculate(100, [['rate'=>10,'is_compound'=>false],['rate'=>5,'is_compound'=>true]]); $this->assertSame(15.5,$r['tax']); }
    public function test_negative_amount_is_rejected(): void { $this->expectException(\InvalidArgumentException::class); app(TaxCalculator::class)->calculate(-1, []); }
}

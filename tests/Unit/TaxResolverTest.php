<?php

namespace ItmoSoft\Tax\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use ItmoSoft\Tax\Models\TaxClass;
use ItmoSoft\Tax\Models\TaxRate;
use ItmoSoft\Tax\Models\TaxRule;
use ItmoSoft\Tax\Services\TaxResolver;
use ItmoSoft\Tax\Tests\TestCase;

class TaxResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_unsaved_tax_class_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(TaxResolver::class)->resolve(new TaxClass());
    }

    public function test_inactive_tax_class_returns_no_rates(): void
    {
        $class = TaxClass::query()->create(['name' => 'Inactive', 'code' => 'inactive', 'is_active' => false]);
        TaxRate::query()->create([
            'tax_class_id' => $class->id,
            'name' => 'VAT',
            'rate' => 15,
            'is_active' => true,
        ]);

        $this->assertCount(0, app(TaxResolver::class)->resolve($class));
    }

    public function test_resolver_uses_most_specific_matching_rule(): void
    {
        $class = TaxClass::query()->create(['name' => 'Standard', 'code' => 'standard']);
        $general = TaxRate::query()->create([
            'tax_class_id' => $class->id, 'name' => 'General VAT', 'rate' => 10, 'is_active' => true,
        ]);
        $specific = TaxRate::query()->create([
            'tax_class_id' => $class->id, 'name' => 'Punjab VAT', 'rate' => 15, 'is_active' => true,
        ]);

        TaxRule::query()->create([
            'tax_class_id' => $class->id, 'tax_rate_id' => $general->id,
            'country' => 'PK', 'priority' => 100, 'is_active' => true,
        ]);
        TaxRule::query()->create([
            'tax_class_id' => $class->id, 'tax_rate_id' => $specific->id,
            'country' => 'PK', 'region' => 'Punjab', 'priority' => 1, 'is_active' => true,
        ]);

        $resolved = app(TaxResolver::class)->resolve($class, ['country' => ' pk ', 'region' => 'Punjab']);

        $this->assertSame([$specific->id], $resolved->modelKeys());
    }

    public function test_resolver_uses_priority_when_specificity_is_equal(): void
    {
        $class = TaxClass::query()->create(['name' => 'Priority', 'code' => 'priority']);
        $lower = TaxRate::query()->create([
            'tax_class_id' => $class->id, 'name' => 'Lower', 'rate' => 5, 'is_active' => true,
        ]);
        $higher = TaxRate::query()->create([
            'tax_class_id' => $class->id, 'name' => 'Higher', 'rate' => 8, 'is_active' => true,
        ]);

        TaxRule::query()->create([
            'tax_class_id' => $class->id, 'tax_rate_id' => $lower->id,
            'country' => 'PK', 'priority' => 1, 'is_active' => true,
        ]);
        TaxRule::query()->create([
            'tax_class_id' => $class->id, 'tax_rate_id' => $higher->id,
            'country' => 'PK', 'priority' => 10, 'is_active' => true,
        ]);

        $resolved = app(TaxResolver::class)->resolve($class, ['country' => 'PK']);

        $this->assertSame([$higher->id], $resolved->modelKeys());
    }

    public function test_resolver_does_not_return_a_rate_from_another_tax_class(): void
    {
        $classA = TaxClass::query()->create(['name' => 'A', 'code' => 'class-a']);
        $classB = TaxClass::query()->create(['name' => 'B', 'code' => 'class-b']);
        $foreignRate = TaxRate::query()->create([
            'tax_class_id' => $classB->id, 'name' => 'Foreign rate', 'rate' => 20, 'is_active' => true,
        ]);

        TaxRule::query()->create([
            'tax_class_id' => $classA->id, 'tax_rate_id' => $foreignRate->id,
            'country' => 'PK', 'priority' => 1, 'is_active' => true,
        ]);

        $resolved = app(TaxResolver::class)->resolve($classA, ['country' => 'PK']);

        $this->assertCount(0, $resolved);
    }

    public function test_resolver_uses_unruled_active_rates_as_defaults(): void
    {
        $class = TaxClass::query()->create(['name' => 'Default', 'code' => 'default']);
        $rate = TaxRate::query()->create([
            'tax_class_id' => $class->id, 'name' => 'Default VAT', 'rate' => 12, 'is_active' => true,
        ]);

        $resolved = app(TaxResolver::class)->resolve($class);

        $this->assertSame([$rate->id], $resolved->modelKeys());
    }
}

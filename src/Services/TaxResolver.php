<?php

namespace ItmoSoft\Tax\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use ItmoSoft\Tax\Models\TaxClass;
use ItmoSoft\Tax\Models\TaxRate;
use ItmoSoft\Tax\Models\TaxRule;

class TaxResolver
{
    public function resolve(TaxClass|int $taxClass, array $location = [], ?CarbonInterface $at = null): Collection
    {
        if ($taxClass instanceof TaxClass) {
            if (! $taxClass->exists || $taxClass->getKey() === null) {
                throw new InvalidArgumentException('The tax class must be persisted before resolving rates.');
            }

            $classId = $taxClass->getKey();
        } else {
            if ($taxClass < 1) {
                throw new InvalidArgumentException('The tax class ID must be a positive integer.');
            }

            $classId = $taxClass;
        }

        $classModel = $this->model('tax_class', TaxClass::class);
        $class = $classModel::query()->find($classId);

        if (! $class || ! (bool) ($class->is_active ?? true)) {
            return collect();
        }

        $at ??= now();

        $rules = $this->model('tax_rule', TaxRule::class)::query()
            ->where('tax_class_id', $classId)
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $at))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', $at))
            ->get();

        $matched = $rules->filter(fn ($rule) => $this->matchesLocation($rule, $location));

        $rateModel = $this->model('tax_rate', TaxRate::class);

        if ($matched->isEmpty()) {
            return $rateModel::query()
                ->where('tax_class_id', $classId)
                ->where('is_active', true)
                ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $at))
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', $at))
                ->whereDoesntHave('rules')
                ->get();
        }

        $specificity = $matched->map(fn ($rule) => $this->specificity($rule))->max();

        $topPriority = $matched
            ->filter(fn ($rule) => $this->specificity($rule) === $specificity)
            ->max('priority');

        $rateIds = $matched
            ->filter(fn ($rule) => $this->specificity($rule) === $specificity && (int) $rule->priority === (int) $topPriority)
            ->pluck('tax_rate_id')
            ->unique()
            ->values();

        return $rateModel::query()
            ->where('tax_class_id', $classId)
            ->whereIn('id', $rateIds)
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $at))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', $at))
            ->get();
    }

    private function matchesLocation(TaxRule $rule, array $location): bool
    {
        foreach (['country', 'region', 'postal_code'] as $key) {
            $expected = $rule->{$key};

            if ($expected === null) {
                continue;
            }

            $actual = $location[$key] ?? null;

            if (! is_string($actual) && ! is_numeric($actual)) {
                return false;
            }

            $expected = trim((string) $expected);
            $actual = trim((string) $actual);

            if ($key === 'country') {
                $expected = strtoupper($expected);
                $actual = strtoupper($actual);
            }

            if ($actual !== $expected) {
                return false;
            }
        }

        return true;
    }

    private function specificity(TaxRule $rule): int
    {
        return (int) ($rule->country !== null)
            + (int) ($rule->region !== null)
            + (int) ($rule->postal_code !== null);
    }

    private function model(string $key, string $default): string
    {
        return config("itmosoft-tax.models.{$key}", $default);
    }
}

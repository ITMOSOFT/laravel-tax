<?php

namespace ItmoSoft\Tax\Services;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use ItmoSoft\Tax\Models\TaxRate;

class TaxCalculator
{
    public function calculate(int|float|string $amount, iterable $rates, bool $inclusive = false, ?int $scale = null): array
    {
        $scale ??= (int) config('itmosoft-tax.rounding.scale', 2);
        $mode = (int) config('itmosoft-tax.rounding.mode', PHP_ROUND_HALF_UP);
        $amount = (float) $amount;

        if (! is_finite($amount) || $amount < 0) {
            throw new InvalidArgumentException('Tax amount must be a finite, non-negative number.');
        }

        if ($scale < 0 || $scale > 14) {
            throw new InvalidArgumentException('Tax rounding scale must be between 0 and 14.');
        }

        $items = ($rates instanceof Collection ? $rates : collect($rates))
            ->map(fn ($rate) => $rate instanceof TaxRate ? $rate : (object) $rate)
            ->values();

        foreach ($items as $rate) {
            if (
                ! isset($rate->rate)
                || ! is_numeric($rate->rate)
                || ! is_finite((float) $rate->rate)
                || (float) $rate->rate < 0
            ) {
                throw new InvalidArgumentException('Each tax rate must be a finite, non-negative numeric percentage.');
            }
        }

        if ($inclusive) {
            $baseRate = $items->where('is_compound', false)->sum(fn ($rate) => (float) $rate->rate);
            $factor = 1 + ($baseRate / 100);

            foreach ($items->where('is_compound', true) as $rate) {
                $factor *= 1 + ((float) $rate->rate / 100);
            }

            $gross = $amount;
            $net = $factor > 0 ? $gross / $factor : $gross;
            $tax = $gross - $net;
        } else {
            $net = $amount;
            $running = $net;
            $tax = 0.0;

            foreach ($items as $rate) {
                $base = (bool) ($rate->is_compound ?? false) ? $running : $net;
                $part = $base * ((float) $rate->rate / 100);
                $tax += $part;
                $running += $part;
            }

            $gross = $net + $tax;
        }

        return [
            'net' => round($net, $scale, $mode),
            'tax' => round($tax, $scale, $mode),
            'gross' => round($gross, $scale, $mode),
            'inclusive' => $inclusive,
            'breakdown' => $items->map(fn ($rate) => [
                'name' => $rate->name ?? 'Tax',
                'rate' => (float) $rate->rate,
                'is_compound' => (bool) ($rate->is_compound ?? false),
            ])->all(),
        ];
    }
}

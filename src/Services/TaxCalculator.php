<?php
namespace ItmoSoft\Tax\Services;
use Illuminate\Support\Collection;
use ItmoSoft\Tax\Models\TaxRate;
class TaxCalculator
{
    public function calculate(int|float|string $amount, iterable $rates, bool $inclusive = false, ?int $scale = null): array
    {
        $scale ??= (int) config('itmosoft-tax.rounding.scale', 2);
        $mode = (int) config('itmosoft-tax.rounding.mode', PHP_ROUND_HALF_UP);
        $amount = (float) $amount;
        if (!is_finite($amount) || $amount < 0) throw new \InvalidArgumentException('Tax amount must be a finite, non-negative number.');
        $items = ($rates instanceof Collection ? $rates : collect($rates))->map(fn ($r) => $r instanceof TaxRate ? $r : (object) $r)->values();
        foreach ($items as $rate) {
            if (!isset($rate->rate) || !is_numeric($rate->rate) || (float) $rate->rate < 0) throw new \InvalidArgumentException('Each tax rate must be a non-negative numeric percentage.');
        }
        if ($inclusive) {
            $baseRate = $items->where('is_compound', false)->sum(fn ($r) => (float) $r->rate);
            $factor = 1 + ($baseRate / 100);
            foreach ($items->where('is_compound', true) as $r) $factor *= 1 + ((float) $r->rate / 100);
            $gross = $amount;
            $net = $factor > 0 ? $gross / $factor : $gross;
            $tax = $gross - $net;
        } else {
            $net = $amount; $running = $net; $tax = 0.0;
            foreach ($items as $r) {
                $base = (bool) ($r->is_compound ?? false) ? $running : $net;
                $part = $base * ((float) $r->rate / 100);
                $tax += $part; $running += $part;
            }
            $gross = $net + $tax;
        }
        return ['net' => round($net, $scale, $mode), 'tax' => round($tax, $scale, $mode), 'gross' => round($gross, $scale, $mode), 'inclusive' => $inclusive,
            'breakdown' => $items->map(fn ($r) => ['name' => $r->name ?? 'Tax', 'rate' => (float) $r->rate, 'is_compound' => (bool) ($r->is_compound ?? false)])->all()];
    }
}

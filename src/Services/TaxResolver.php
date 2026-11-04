<?php
namespace ItmoSoft\Tax\Services;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use ItmoSoft\Tax\Models\TaxClass;
use ItmoSoft\Tax\Models\TaxRate;
use ItmoSoft\Tax\Models\TaxRule;
class TaxResolver
{
    public function resolve(TaxClass|int $taxClass, array $location = [], ?CarbonInterface $at = null): Collection
    {
        $classId = $taxClass instanceof TaxClass ? $taxClass->getKey() : $taxClass;
        $at ??= now();
        $rules = $this->model('tax_rule', TaxRule::class)::query()->where('tax_class_id', $classId)->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $at))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $at))->get();
        $matched = $rules->filter(function ($rule) use ($location) {
            foreach (['country', 'region', 'postal_code'] as $key) {
                if ($rule->{$key} !== null && (string) ($location[$key] ?? '') !== (string) $rule->{$key}) return false;
            }
            return true;
        });
        if ($matched->isEmpty()) {
            $rateModel = $this->model('tax_rate', TaxRate::class);
            return $rateModel::query()->where('tax_class_id', $classId)->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $at))
                ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $at))
                ->whereDoesntHave('rules')->get();
        }
        $specificity = $matched->map(fn ($r) => (int) ($r->country !== null) + (int) ($r->region !== null) + (int) ($r->postal_code !== null))->max();
        $top = $matched->filter(fn ($r) => ((int) ($r->country !== null) + (int) ($r->region !== null) + (int) ($r->postal_code !== null)) === $specificity)->max('priority');
        $ids = $matched->filter(fn ($r) => ((int) ($r->country !== null) + (int) ($r->region !== null) + (int) ($r->postal_code !== null)) === $specificity && $r->priority === $top)->pluck('tax_rate_id')->unique();
        $rateModel = $this->model('tax_rate', TaxRate::class);
        return $rateModel::query()->whereIn('id', $ids)->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $at))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $at))->get();
    }
    private function model(string $key, string $default): string { return config("itmosoft-tax.models.{$key}", $default); }
}

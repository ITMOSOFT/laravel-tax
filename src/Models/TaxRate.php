<?php
namespace ItmoSoft\Tax\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TaxRate extends Model
{
    protected $guarded = [];
    protected $casts = ['rate' => 'decimal:6', 'is_compound' => 'boolean', 'is_active' => 'boolean', 'starts_at' => 'datetime', 'ends_at' => 'datetime'];
    public function getTable(): string { return config('itmosoft-tax.table_prefix', 'tax_').'tax_rates'; }
    public function taxClass(): BelongsTo { return $this->belongsTo(config('itmosoft-tax.models.tax_class', TaxClass::class)); }
    public function rules(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(config('itmosoft-tax.models.tax_rule', TaxRule::class), 'tax_rate_id'); }
}

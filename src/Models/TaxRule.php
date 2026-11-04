<?php
namespace ItmoSoft\Tax\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TaxRule extends Model
{
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean', 'priority' => 'integer', 'starts_at' => 'datetime', 'ends_at' => 'datetime'];
    public function getTable(): string { return config('itmosoft-tax.table_prefix', 'tax_').'tax_rules'; }
    public function taxClass(): BelongsTo { return $this->belongsTo(config('itmosoft-tax.models.tax_class', TaxClass::class)); }
    public function taxRate(): BelongsTo { return $this->belongsTo(config('itmosoft-tax.models.tax_rate', TaxRate::class)); }
}

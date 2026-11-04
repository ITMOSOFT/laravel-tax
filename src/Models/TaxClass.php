<?php
namespace ItmoSoft\Tax\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class TaxClass extends Model
{
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];
    public function getTable(): string { return config('itmosoft-tax.table_prefix', 'tax_').'tax_classes'; }
    public function rates(): HasMany { return $this->hasMany(config('itmosoft-tax.models.tax_rate', TaxRate::class), 'tax_class_id'); }
    public function rules(): HasMany { return $this->hasMany(config('itmosoft-tax.models.tax_rule', TaxRule::class), 'tax_class_id'); }
}

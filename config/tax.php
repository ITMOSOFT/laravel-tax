<?php
return [
    'table_prefix' => env('ITMOSOFT_TAX_TABLE_PREFIX', 'tax_'),
    'models' => [
        'tax_class' => \ItmoSoft\Tax\Models\TaxClass::class,
        'tax_rate' => \ItmoSoft\Tax\Models\TaxRate::class,
        'tax_rule' => \ItmoSoft\Tax\Models\TaxRule::class,
    ],
    'rounding' => ['scale' => 2, 'mode' => PHP_ROUND_HALF_UP],
];

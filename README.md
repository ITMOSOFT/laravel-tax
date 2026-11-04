# ITMOSOFT Laravel Tax

Reusable tax engine for Laravel 12 and 13 / PHP 8.3+.

## Features
- Tax classes and multiple tax rates
- Inclusive and exclusive calculations
- Compound and non-compound rates
- Location rules for country, region, and postal code
- Scheduled rates and rules
- Configurable table prefix and model classes

## Install
```bash
composer require itmosoft/laravel-tax
php artisan vendor:publish --tag=itmosoft-tax-config
php artisan migrate
```

## Create a class and rate
```php
use ItmoSoft\Tax\Models\TaxClass;
use ItmoSoft\Tax\Models\TaxRate;
$class = TaxClass::create(['name' => 'Standard VAT', 'code' => 'standard']);
$rate = TaxRate::create(['tax_class_id' => $class->id, 'name' => 'VAT', 'rate' => 15, 'is_active' => true]);
```

## Calculate
```php
$result = app(\ItmoSoft\Tax\Services\TaxCalculator::class)->calculate(100, [$rate], inclusive: false);
// net: 100.00, tax: 15.00, gross: 115.00
```

Pass `inclusive: true` to extract tax from a gross amount. Rates are percentages. Compound rates apply to the running taxable base.

## Resolve location rates
Create `TaxRule` records linking a tax class and rate. `country`, `region`, and `postal_code` are optional; null fields are wildcards. The resolver selects the most specific matching rule, then the highest priority. If no rule matches, rates with no rules attached are treated as defaults.

```php
$rates = app(\ItmoSoft\Tax\Services\TaxResolver::class)->resolve($class, [
    'country' => 'PK', 'region' => 'Punjab', 'postal_code' => '60000',
]);
```

## Boundaries
The package calculates supplied rates; it does not determine legal obligations, automatically manage exemptions, convert currencies, file returns, or provide jurisdiction-specific legal advice. Have jurisdiction rules reviewed by a qualified tax professional before production use.

## Test
```bash
composer install
vendor/bin/phpunit
```

MIT License.

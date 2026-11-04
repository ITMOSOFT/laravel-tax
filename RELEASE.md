# Release Guide — ITMOSOFT Laravel Tax

## Repository
- Name: `ITMOSOFT/laravel-tax`
- Description: A reusable Laravel tax package with tax classes, rates, location-based rules, and inclusive or exclusive tax calculations. Supports Laravel 12 and 13.

## Validate
```bash
composer validate
composer install
vendor/bin/phpunit
```
Do not publish until local tests and both Laravel 12/13 GitHub Actions jobs pass.

## Initial GitHub setup
Create https://github.com/ITMOSOFT/laravel-tax, then run:
```bash
git init
git branch -M main
git remote add origin https://github.com/ITMOSOFT/laravel-tax.git
git add .
git commit -m "release: laravel tax v1.0.0"
git push -u origin main
```

## Subsequent commits
```bash
git add .
git commit -m "fix: describe the change"
git push origin main
```

## Tag
```bash
git tag -a v1.0.0 -m "Laravel Tax v1.0.0"
git push origin v1.0.0
git show v1.0.0
```

## GitHub Release
Title: `v1.0.0 — Initial Stable Release`

Description:
```markdown
## ITMOSOFT Laravel Tax v1.0.0
Initial stable release of a reusable Laravel tax engine.

### Features
- Tax classes and multiple tax rates
- Inclusive and exclusive tax calculations
- Compound and non-compound rates
- Location-based rules for country, region, and postal code
- Scheduled rates and rules
- Configurable table prefix and model classes

### Compatibility
- PHP 8.3+
- Laravel 12 and 13

### Installation
composer require itmosoft/laravel-tax:^1.0
php artisan vendor:publish --tag=itmosoft-tax-config
php artisan migrate
```
Create it at https://github.com/ITMOSOFT/laravel-tax/releases/new and select `v1.0.0`.

## Packagist
1. Sign in at https://packagist.org/.
2. Submit https://github.com/ITMOSOFT/laravel-tax at https://packagist.org/packages/submit.
3. Confirm package name `itmosoft/laravel-tax`.
4. Configure the GitHub webhook using the exact URL/secret Packagist provides, if not configured automatically.
5. Verify https://packagist.org/packages/itmosoft/laravel-tax.
6. In a clean Laravel app run:
```bash
composer clear-cache
composer require itmosoft/laravel-tax:^1.0
composer show itmosoft/laravel-tax
```
Do not claim publication until Packagist page and clean installation are verified.

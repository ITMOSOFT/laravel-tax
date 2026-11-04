<?php
namespace ItmoSoft\Tax;
use Illuminate\Support\ServiceProvider;
use ItmoSoft\Tax\Services\TaxCalculator;
use ItmoSoft\Tax\Services\TaxResolver;
class TaxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tax.php', 'itmosoft-tax');
        $this->app->singleton(TaxResolver::class);
        $this->app->singleton(TaxCalculator::class);
    }
    public function boot(): void
    {
        $this->publishes([__DIR__.'/../config/tax.php' => config_path('itmosoft-tax.php')], 'itmosoft-tax-config');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}

<?php
namespace ItmoSoft\Tax\Tests;
use ItmoSoft\Tax\TaxServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
abstract class TestCase extends Orchestra { protected function getPackageProviders($app): array { return [TaxServiceProvider::class]; } }

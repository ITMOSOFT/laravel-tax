<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        $prefix = config('itmosoft-tax.table_prefix', 'tax_');
        Schema::create($prefix.'tax_classes', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('code')->unique(); $table->text('description')->nullable(); $table->boolean('is_active')->default(true); $table->timestamps(); });
        Schema::create($prefix.'tax_rates', function (Blueprint $table) use ($prefix) { $table->id(); $table->foreignId('tax_class_id')->constrained($prefix.'tax_classes')->cascadeOnDelete(); $table->string('name'); $table->decimal('rate', 10, 6); $table->boolean('is_compound')->default(false); $table->boolean('is_active')->default(true); $table->timestamp('starts_at')->nullable(); $table->timestamp('ends_at')->nullable(); $table->timestamps(); $table->index(['tax_class_id', 'is_active']); });
        Schema::create($prefix.'tax_rules', function (Blueprint $table) use ($prefix) { $table->id(); $table->foreignId('tax_class_id')->constrained($prefix.'tax_classes')->cascadeOnDelete(); $table->foreignId('tax_rate_id')->constrained($prefix.'tax_rates')->cascadeOnDelete(); $table->string('country', 2)->nullable(); $table->string('region')->nullable(); $table->string('postal_code')->nullable(); $table->unsignedInteger('priority')->default(0); $table->boolean('is_active')->default(true); $table->timestamp('starts_at')->nullable(); $table->timestamp('ends_at')->nullable(); $table->timestamps(); $table->index(['tax_class_id', 'country', 'region', 'postal_code']); });
    }
    public function down(): void { $prefix = config('itmosoft-tax.table_prefix', 'tax_'); Schema::dropIfExists($prefix.'tax_rules'); Schema::dropIfExists($prefix.'tax_rates'); Schema::dropIfExists($prefix.'tax_classes'); }
};

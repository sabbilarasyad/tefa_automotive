<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('phone', 30)->index();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained();
            $table->string('plate_number', 20)->index();
            $table->string('brand', 80);
            $table->string('model', 100);
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('color', 50)->nullable();
            $table->string('vin', 100)->nullable()->unique();
            $table->string('engine_number', 100)->nullable();
            $table->unsignedInteger('current_km')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->boolean('documentation_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('spareparts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->string('unit', 30)->default('pcs');
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->decimal('stock_qty', 15, 3)->default(0);
            $table->decimal('min_stock', 15, 3)->default(0);
            $table->boolean('documentation_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('document_number_counters', function (Blueprint $table) {
            $table->id();
            $table->enum('document_type', ['PKB', 'INVOICE']);
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('last_number')->default(0);
            $table->unique(['document_type', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_number_counters');
        Schema::dropIfExists('spareparts');
        Schema::dropIfExists('services');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('customers');
    }
};
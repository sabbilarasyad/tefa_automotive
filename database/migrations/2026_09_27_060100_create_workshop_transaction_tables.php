<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pkb', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->unique();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('vehicle_id')->constrained();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('mechanic_id')->nullable()->constrained('users');
            $table->text('complaint');
            $table->text('inspection_result')->nullable();
            $table->enum('status', ['WAITING_PARTS', 'WAITING_MECHANIC', 'IN_PROGRESS', 'WAITING_PAYMENT', 'PAID', 'CLOSED', 'CANCELLED'])->default('WAITING_MECHANIC');
            $table->dateTime('service_date');
            $table->decimal('estimated_total', 15, 2)->default(0);
            $table->decimal('final_total', 15, 2)->default(0);
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();
            $table->index(['mechanic_id', 'status']);
            $table->index('service_date');
        });

        Schema::create('pkb_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pkb_id')->constrained('pkb')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services');
            $table->string('name_snapshot', 150);
            $table->decimal('qty', 15, 3)->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->boolean('documentation_required')->default(false);
            $table->boolean('is_done')->default(false);
            $table->index('pkb_id');
        });

        Schema::create('pkb_spareparts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pkb_id')->constrained('pkb')->cascadeOnDelete();
            $table->foreignId('sparepart_id')->constrained('spareparts');
            $table->string('name_snapshot', 150);
            $table->decimal('qty_planned', 15, 3)->default(0);
            $table->decimal('qty_used', 15, 3)->nullable();
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->boolean('documentation_required')->default(false);
            $table->index('pkb_id');
            $table->index('sparepart_id');
        });

        Schema::create('pkb_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pkb_id')->constrained('pkb')->cascadeOnDelete();
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50);
            $table->foreignId('changed_by')->nullable()->constrained('users');
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['pkb_id', 'created_at']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sparepart_id')->constrained('spareparts');
            $table->enum('type', ['OUT', 'MANUAL_IN', 'ADJUSTMENT', 'RETURN']);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('qty_in', 15, 3)->default(0);
            $table->decimal('qty_out', 15, 3)->default(0);
            $table->decimal('stock_before', 15, 3);
            $table->decimal('stock_after', 15, 3);
            $table->string('reason', 255)->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['sparepart_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('documentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pkb_id')->constrained('pkb')->cascadeOnDelete();
            $table->foreignId('pkb_service_id')->nullable()->constrained('pkb_services')->cascadeOnDelete();
            $table->foreignId('pkb_sparepart_id')->nullable()->constrained('pkb_spareparts')->cascadeOnDelete();
            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->dateTime('uploaded_at')->useCurrent();
            $table->index('pkb_id');
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->unique();
            $table->foreignId('pkb_id')->unique()->constrained('pkb');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->enum('status', ['UNPAID', 'PAID', 'CANCELLED'])->default('UNPAID');
            $table->dateTime('issued_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->enum('method', ['CASH', 'TRANSFER', 'QRIS', 'OTHER']);
            $table->decimal('amount', 15, 2);
            $table->string('reference_number', 100)->nullable();
            $table->dateTime('paid_at');
            $table->foreignId('received_by')->constrained('users');
            $table->index(['invoice_id', 'paid_at']);
        });

        Schema::create('service_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pkb_id')->unique()->constrained('pkb');
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->dateTime('service_date');
            $table->unsignedInteger('km')->nullable();
            $table->text('summary')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->timestamp('created_at')->nullable();
            $table->index(['vehicle_id', 'service_date']);
        });

        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pkb_id')->constrained('pkb');
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->foreignId('customer_id')->constrained('customers');
            $table->date('reminder_date');
            $table->string('type', 50)->default('SERVICE_6_MONTHS');
            $table->enum('status', ['PENDING', 'SENT', 'CANCELLED'])->default('PENDING');
            $table->dateTime('sent_at')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
            $table->index(['reminder_date', 'status']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);
            $table->string('entity_type', 100)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['entity_type', 'entity_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('reminders');
        Schema::dropIfExists('service_histories');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('documentations');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('pkb_status_histories');
        Schema::dropIfExists('pkb_spareparts');
        Schema::dropIfExists('pkb_services');
        Schema::dropIfExists('pkb');
    }
};
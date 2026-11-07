<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('name', 120);
            $table->string('tagline', 255)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('price');
            $table->boolean('price_validated')->default(false);
            $table->unsignedSmallInteger('duration_minutes');
            $table->string('duration_label', 80)->nullable();
            $table->json('features')->nullable();
            $table->text('terms')->nullable();
            $table->unsignedSmallInteger('max_products')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 190)->index();
            $table->string('phone', 40);
            $table->string('whatsapp', 40);
            $table->string('country', 80)->index();
            $table->string('city', 100);
            $table->string('timezone', 64)->default('Africa/Lome');
            $table->boolean('is_demo')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name', 150);
            $table->string('sector', 100)->index();
            $table->text('description');
            $table->text('offer_nature');
            $table->string('business_phone', 40);
            $table->string('business_address', 255);
            $table->json('social_links')->nullable();
            $table->string('logo_path')->nullable();
            $table->boolean('is_food')->default(false);
            $table->text('food_preparation')->nullable();
            $table->text('food_conservation')->nullable();
            $table->text('food_presentation')->nullable();
            $table->boolean('is_demo')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('business_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->unsignedInteger('quantity_available')->nullable();
            $table->string('price_note', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('availability_slots', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            // null = suit les réglages hebdomadaires par défaut
            $table->boolean('is_open')->nullable();
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->string('note', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('slot_blocks', function (Blueprint $table) {
            $table->id();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('reason', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['starts_at', 'ends_at']);
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('business_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('package_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('mode', 10)->index();
            $table->date('booking_date')->index();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedInteger('amount');
            $table->string('currency', 10)->default('XOF');
            $table->string('status', 20)->default('PENDING_PAYMENT')->index();
            $table->string('participant_timezone', 64)->nullable();
            $table->string('connection_type', 20)->nullable();
            $table->boolean('needs_assistance')->default(false);
            $table->text('assistance_notes')->nullable();
            $table->unsignedSmallInteger('products_count')->default(1);
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('stream_link')->nullable(); // future intégration visio / diffusion
            $table->timestamp('payment_due_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('cancellation_reason', 255)->nullable();
            $table->boolean('is_demo')->default(false)->index();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['starts_at', 'ends_at']);
            $table->index(['booking_date', 'status']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 30)->default('manual');
            $table->string('method', 30)->nullable(); // mobile_money, card, cash, transfer
            $table->unsignedInteger('amount');
            $table->string('currency', 10)->default('XOF');
            $table->string('status', 20)->default('PENDING')->index();
            $table->string('provider_reference', 120)->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('booking_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // logo, product_photo
            $table->string('disk', 30)->default('local');
            $table->string('path');
            $table->string('original_name', 255);
            $table->string('mime_type', 100);
            $table->unsignedInteger('size');
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 100);
            $table->nullableMorphs('subject');
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('booking_attachments');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('slot_blocks');
        Schema::dropIfExists('availability_slots');
        Schema::dropIfExists('business_products');
        Schema::dropIfExists('businesses');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('settings');
    }
};

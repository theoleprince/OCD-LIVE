<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();
            $table->string('tracking_token', 64)->unique();
            $table->foreignId('seller_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            // Dénormalisés depuis le vendeur au moment de la soumission : filtres publics rapides sans jointure.
            $table->foreignId('country_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('city_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('live_schedules')->nullOnDelete();
            $table->foreignId('moderator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('platform', 20)->default('tiktok');
            $table->string('account_name', 100);
            $table->string('live_url', 255);
            $table->text('product_description');
            $table->json('consents')->nullable();
            $table->string('status', 20)->default('PENDING');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('rejection_reason', 255)->nullable();
            $table->text('internal_comment')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['status', 'expires_at']);
            $table->index(['status', 'country_id']);
            $table->index(['status', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_submissions');
    }
};

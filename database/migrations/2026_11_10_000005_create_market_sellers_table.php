<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 150);
            $table->string('business_name', 150);
            $table->string('phone', 40);
            $table->foreignId('country_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('city_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->text('description')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('logo_image')->nullable();
            $table->string('public_slug', 160)->nullable()->unique();
            $table->json('consents')->nullable();
            $table->timestamps();
            $table->index(['phone', 'country_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};

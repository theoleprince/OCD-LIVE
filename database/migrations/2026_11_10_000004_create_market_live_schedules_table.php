<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->time('start_time');
            $table->time('end_time');
            // Les horaires sont interprétés dans le fuseau du pays du vendeur ('country') ou un fuseau fixe ('fixed').
            $table->string('timezone_policy', 20)->default('country');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_schedules');
    }
};

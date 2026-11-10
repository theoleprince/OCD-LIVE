<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('live_submissions')->cascadeOnDelete();
            $table->string('reporter_contact', 190)->nullable();
            $table->string('reason', 50);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('OPEN')->index();
            $table->timestamp('resolved_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_reports');
    }
};

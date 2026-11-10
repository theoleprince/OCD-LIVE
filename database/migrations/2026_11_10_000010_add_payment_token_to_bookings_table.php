<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Jeton imprévisible donnant accès à la page de paiement d'une réservation (/paiement/{token}),
 * sans compte client. Les réservations existantes reçoivent un jeton.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_token', 64)->nullable()->unique()->after('reference');
        });

        DB::table('bookings')->whereNull('payment_token')->orderBy('id')->each(function ($booking) {
            DB::table('bookings')->where('id', $booking->id)->update(['payment_token' => Str::random(40)]);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique(['payment_token']);
            $table->dropColumn('payment_token');
        });
    }
};

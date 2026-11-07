<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\AdminActivityLog;
use App\Models\Booking;
use Illuminate\Console\Command;

class ExpireUnpaidBookings extends Command
{
    protected $signature = 'ocd:expire-unpaid {--dry-run : Affiche sans modifier}';

    protected $description = 'Annule les réservations restées sans paiement au-delà du délai de blocage, pour libérer leurs créneaux.';

    public function handle(): int
    {
        $expired = Booking::query()
            ->where('status', BookingStatus::PendingPayment->value)
            ->where('is_demo', false)
            ->whereNotNull('payment_due_at')
            ->where('payment_due_at', '<', now())
            ->get();

        foreach ($expired as $booking) {
            $this->line(($this->option('dry-run') ? '[simulation] ' : '').$booking->reference.' — échéance '.$booking->payment_due_at->format('d/m/Y H:i'));

            if (! $this->option('dry-run')) {
                $booking->update(['status' => BookingStatus::Cancelled, 'cancelled_at' => now(), 'cancellation_reason' => 'Délai de paiement dépassé']);
                $booking->payments()->where('status', PaymentStatus::Pending->value)->update(['status' => PaymentStatus::Failed->value]);
                AdminActivityLog::record('bookings.expired', $booking);
            }
        }

        $this->info($expired->count().' réservation(s) '.($this->option('dry-run') ? 'concernée(s).' : 'expirée(s).'));

        return self::SUCCESS;
    }
}

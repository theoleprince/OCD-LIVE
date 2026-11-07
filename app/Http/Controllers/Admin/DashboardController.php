<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now(config('ocd.timezone'))->toDateString();

        // Toutes les statistiques sont calculées sur les données réelles de la base.
        $stats = [
            'total' => Booking::count(),
            'today' => Booking::whereDate('booking_date', $today)->whereNot('status', BookingStatus::Cancelled->value)->count(),
            'pending' => Booking::whereIn('status', [BookingStatus::PendingPayment->value, BookingStatus::PendingReview->value])->count(),
            'pending_payment' => Booking::where('status', BookingStatus::PendingPayment->value)->count(),
            'pending_review' => Booking::where('status', BookingStatus::PendingReview->value)->count(),
            'confirmed' => Booking::where('status', BookingStatus::Confirmed->value)->count(),
            'revenue' => (int) Payment::where('status', PaymentStatus::Paid->value)
                ->whereHas('booking', fn ($q) => $q->where('is_demo', false))
                ->sum('amount'),
            'customers' => Customer::count(),
            'businesses' => Business::count(),
            'demo' => Booking::where('is_demo', true)->count(),
        ];

        $byPackage = Package::withTrashed()
            ->withCount(['bookings' => fn ($q) => $q->whereNot('status', BookingStatus::Cancelled->value)])
            ->orderBy('sort_order')
            ->get(['id', 'name', 'deleted_at']);

        $upcoming = Booking::with(['customer', 'business', 'package'])
            ->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::PendingReview->value, BookingStatus::PendingPayment->value])
            ->where('starts_at', '>=', now()->startOfDay())
            ->orderBy('starts_at')
            ->limit(6)
            ->get();

        $remoteAssistance = Booking::where('mode', 'REMOTE')
            ->where('needs_assistance', true)
            ->whereNotIn('status', [BookingStatus::Cancelled->value, BookingStatus::Completed->value])
            ->count();

        return view('admin.dashboard', compact('stats', 'byPackage', 'upcoming', 'remoteAssistance'));
    }
}

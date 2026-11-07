<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\ParticipationMode;
use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Booking;
use App\Models\BookingAttachment;
use App\Models\Package;
use App\Models\Payment;
use App\Services\BookingWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingController extends Controller
{
    public function __construct(private BookingWorkflow $workflow) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(BookingStatus::class)],
            'mode' => ['nullable', Rule::enum(ParticipationMode::class)],
            'package' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $bookings = Booking::query()
            ->with(['customer', 'business', 'package', 'latestPayment'])
            ->when($filters['q'] ?? null, function ($q, $term) {
                $q->where(function ($q) use ($term) {
                    $q->where('reference', 'like', "%$term%")
                        ->orWhereHas('customer', fn ($c) => $c->where('last_name', 'like', "%$term%")
                            ->orWhere('first_name', 'like', "%$term%")
                            ->orWhere('email', 'like', "%$term%")
                            ->orWhere('phone', 'like', "%$term%"))
                        ->orWhereHas('business', fn ($b) => $b->where('name', 'like', "%$term%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['mode'] ?? null, fn ($q, $v) => $q->where('mode', $v))
            ->when($filters['package'] ?? null, fn ($q, $v) => $q->where('package_id', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('booking_date', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('booking_date', '<=', $v))
            ->orderByDesc('starts_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'filters' => $filters,
            'packages' => Package::withTrashed()->orderBy('sort_order')->get(['id', 'name']),
        ]);
    }

    public function show(Booking $booking): View
    {
        $booking->load(['customer', 'business.products', 'package', 'payments.recorder', 'attachments']);

        $history = AdminActivityLog::with('user')
            ->where('subject_type', $booking->getMorphClass())
            ->where('subject_id', $booking->id)
            ->latest()
            ->get();

        return view('admin.bookings.show', compact('booking', 'history'));
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'stream_link' => ['nullable', 'url', 'max:255'],
        ]);

        $booking->update($data);
        AdminActivityLog::record('booking.updated', $booking, array_keys($data));

        return back()->with('status', 'Notes mises à jour.');
    }

    public function recordPayment(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'method' => ['required', Rule::in(array_keys(Payment::methodLabels()))],
            'reference' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:500'],
            'confirm_received' => ['accepted'],
        ], ['confirm_received.accepted' => 'Confirmez que le paiement a bien été reçu.']);

        $this->workflow->recordPayment($booking, $data['method'], $data['reference'] ?? null, $data['note'] ?? null);

        return back()->with('status', 'Paiement enregistré. La réservation est en attente de validation.');
    }

    public function failPayment(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate(['note' => ['nullable', 'string', 'max:500']]);
        $this->workflow->markPaymentFailed($booking, $data['note'] ?? null);

        return back()->with('status', 'Paiement marqué comme échoué. Un nouveau paiement en attente a été ouvert.');
    }

    public function refundPayment(Booking $booking, Payment $payment): RedirectResponse
    {
        abort_unless($payment->booking_id === $booking->id, 404);
        $this->workflow->refund($payment);

        return back()->with('status', 'Paiement marqué comme remboursé.');
    }

    public function confirm(Booking $booking): RedirectResponse
    {
        $this->workflow->confirm($booking);

        return back()->with('status', 'Réservation confirmée. Un e-mail de confirmation a été envoyé au client.');
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:255']]);
        $this->workflow->cancel($booking, $data['reason'] ?? null);

        return back()->with('status', 'Réservation annulée. Le créneau est de nouveau disponible.');
    }

    public function complete(Booking $booking): RedirectResponse
    {
        $this->workflow->complete($booking);

        return back()->with('status', 'Réservation marquée comme terminée.');
    }

    public function attachment(Booking $booking, BookingAttachment $attachment): StreamedResponse
    {
        abort_unless($attachment->booking_id === $booking->id, 404);
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        return Storage::disk($attachment->disk)->response($attachment->path, $attachment->original_name, [
            'Content-Type' => $attachment->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=600',
        ]);
    }
}

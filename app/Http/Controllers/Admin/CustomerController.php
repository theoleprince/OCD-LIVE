<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $term = $request->string('q')->limit(100)->toString();

        $customers = Customer::query()
            ->withCount('bookings')
            ->with('businesses:id,customer_id,name,sector')
            ->when($term, fn ($q) => $q->where(fn ($q) => $q
                ->where('last_name', 'like', "%$term%")
                ->orWhere('first_name', 'like', "%$term%")
                ->orWhere('email', 'like', "%$term%")
                ->orWhere('phone', 'like', "%$term%")
                ->orWhere('country', 'like', "%$term%")
                ->orWhereHas('businesses', fn ($b) => $b->where('name', 'like', "%$term%"))))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.customers.index', compact('customers', 'term'));
    }

    public function show(Customer $customer): View
    {
        $customer->load(['businesses.products', 'bookings' => fn ($q) => $q->with(['package', 'business', 'latestPayment'])->orderByDesc('starts_at')]);

        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Export CSV (séparateur « ; » pour Excel FR). Réservé aux administrateurs et journalisé.
     */
    public function export(): StreamedResponse
    {
        AdminActivityLog::record('customers.exported');

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['ID', 'Nom', 'Prénom', 'E-mail', 'Téléphone', 'WhatsApp', 'Pays', 'Ville', 'Business', 'Secteur', 'Réservations', 'Démo', 'Créé le'], ';');

            Customer::query()->with('businesses:id,customer_id,name,sector')->withCount('bookings')->orderBy('id')
                ->chunk(500, function ($customers) use ($out) {
                    foreach ($customers as $c) {
                        fputcsv($out, array_map([$this, 'csvSafe'], [
                            $c->id, $c->last_name, $c->first_name, $c->email, $c->phone, $c->whatsapp, $c->country, $c->city,
                            $c->businesses->pluck('name')->implode(' | '), $c->businesses->pluck('sector')->unique()->implode(' | '),
                            $c->bookings_count, $c->is_demo ? 'oui' : 'non', $c->created_at->format('Y-m-d H:i'),
                        ]), ';');
                    }
                });

            fclose($out);
        }, 'ocd-live-clients-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Neutralise l'injection de formules dans les tableurs. */
    public function csvSafe(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}

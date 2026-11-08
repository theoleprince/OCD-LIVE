@extends('layouts.admin')

@section('title', 'Paramètres')

@section('content')
    @php($weekdays = array_map('intval', explode(',', (string) $values['open_weekdays'])))
    <form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-4xl space-y-4">
        @csrf @method('PUT')

        <section class="card p-5 sm:p-8">
            <h2 class="font-semibold text-ink">Disponibilités hebdomadaires</h2>
            <p class="mt-1 text-sm text-neutral-500">Règles par défaut. Chaque date peut être ajustée depuis le calendrier.</p>
            <div class="mt-5">
                <p class="mb-2 text-sm font-medium">Jours ouverts</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ([1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'] as $n => $label)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="open_weekdays[]" value="{{ $n }}" @checked(in_array($n, old('open_weekdays', $weekdays))) class="peer sr-only">
                            <span class="block rounded-full border border-neutral-300 px-4 py-2 text-sm peer-checked:border-brand-600 peer-checked:bg-brand-600 peer-checked:text-white">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="mt-5 grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['opening_time', 'Ouverture', 'time'], ['closing_time', 'Fermeture', 'time'],
                    ['buffer_minutes', 'Marge entre deux émissions (min)', 'number'],
                    ['booking_start_date', 'Première date réservable', 'date'],
                    ['booking_horizon_days', 'Horizon de réservation (jours)', 'number'],
                    ['min_notice_hours', 'Délai minimum avant émission (h)', 'number'],
                    ['payment_hold_hours', 'Durée de blocage sans paiement (h)', 'number'],
                ] as [$key, $label, $type])
                    <label class="text-sm font-medium">{{ $label }}
                        <input type="{{ $type }}" name="{{ $key }}" value="{{ old($key, $values[$key]) }}" required class="field mt-1.5 w-full">
                    </label>
                @endforeach
                <label class="text-sm font-medium">Pas des créneaux
                    <select name="slot_step_minutes" class="field mt-1.5 w-full">
                        @foreach ([15, 30, 60] as $s)<option value="{{ $s }}" @selected((int) old('slot_step_minutes', $values['slot_step_minutes']) === $s)>Toutes les {{ $s }} min</option>@endforeach
                    </select>
                </label>
            </div>
        </section>

        <section class="card p-5 sm:p-8">
            <h2 class="font-semibold text-ink">Coordonnées publiques</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                @foreach ([
                    ['contact_phone', 'Téléphone', 'text'], ['contact_whatsapp', 'WhatsApp (format international)', 'text'],
                    ['contact_email', 'E-mail', 'email'], ['contact_address', 'Adresse du studio', 'text'],
                    ['social_facebook', 'Facebook (URL)', 'url'], ['social_instagram', 'Instagram (URL)', 'url'],
                    ['social_tiktok', 'TikTok (URL)', 'url'], ['social_youtube', 'YouTube (URL)', 'url'],
                ] as [$key, $label, $type])
                    <label class="text-sm font-medium">{{ $label }}
                        <input type="{{ $type }}" name="{{ $key }}" value="{{ old($key, $values[$key]) }}" @if (! str_starts_with($key, 'social_')) required @endif class="field mt-1.5 w-full">
                    </label>
                @endforeach
            </div>
        </section>

        <section class="card p-5 sm:p-8">
            <h2 class="font-semibold text-ink">Notifications</h2>
            <label class="mt-4 block text-sm font-medium">E-mail supplémentaire pour les nouvelles réservations
                <input type="email" name="admin_notification_email" value="{{ old('admin_notification_email', $values['admin_notification_email']) }}" class="field mt-1.5 w-full sm:w-1/2">
            </label>
            <p class="mt-1 text-xs text-neutral-500">Tous les comptes de l'équipe reçoivent déjà les notifications dans le tableau de bord et par e-mail.</p>
        </section>

        <button class="btn-primary">Enregistrer les paramètres</button>
    </form>
@endsection

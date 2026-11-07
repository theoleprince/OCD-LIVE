<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        $values = collect(array_keys(config('ocd.defaults')))->mapWithKeys(fn ($k) => [$k => setting($k)]);

        return view('admin.settings.edit', ['values' => $values]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'contact_phone' => ['required', 'string', 'max:40'],
            'contact_whatsapp' => ['required', 'string', 'max:40', 'regex:/^\+?[0-9 ]{6,25}$/'],
            'contact_email' => ['required', 'email', 'max:190'],
            'contact_address' => ['required', 'string', 'max:255'],
            'social_facebook' => ['nullable', 'url', 'max:255'],
            'social_instagram' => ['nullable', 'url', 'max:255'],
            'social_tiktok' => ['nullable', 'url', 'max:255'],
            'social_youtube' => ['nullable', 'url', 'max:255'],
            'opening_time' => ['required', 'date_format:H:i'],
            'closing_time' => ['required', 'date_format:H:i', 'after:opening_time'],
            'open_weekdays' => ['required', 'array', 'min:1'],
            'open_weekdays.*' => ['integer', 'between:1,7'],
            'buffer_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'slot_step_minutes' => ['required', 'integer', 'in:15,30,60'],
            'booking_start_date' => ['required', 'date_format:Y-m-d'],
            'booking_horizon_days' => ['required', 'integer', 'min:7', 'max:730'],
            'min_notice_hours' => ['required', 'integer', 'min:0', 'max:720'],
            'payment_hold_hours' => ['required', 'integer', 'min:1', 'max:720'],
            'admin_notification_email' => ['nullable', 'email', 'max:190'],
        ]);

        $data['open_weekdays'] = collect($data['open_weekdays'])->map('intval')->unique()->sort()->implode(',');

        foreach ($data as $key => $value) {
            Setting::put($key, $value);
        }

        AdminActivityLog::record('settings.updated', null, array_keys($data));

        return back()->with('status', 'Paramètres enregistrés.');
    }
}

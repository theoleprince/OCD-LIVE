<?php

namespace App\Http\Controllers\Admin\Market;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\LiveSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        return view('admin.market.schedules.index', [
            'schedules' => LiveSchedule::ordered()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $schedule = LiveSchedule::create([
            ...$data,
            'timezone_policy' => 'country',
            'is_active' => true,
            'sort_order' => (int) LiveSchedule::max('sort_order') + 1,
        ]);

        AdminActivityLog::record('market.schedule.created', $schedule);

        return back()->with('status', 'Créneau « '.$schedule->name.' » créé.');
    }

    public function toggle(LiveSchedule $schedule): RedirectResponse
    {
        $schedule->update(['is_active' => ! $schedule->is_active]);
        AdminActivityLog::record($schedule->is_active ? 'market.schedule.activated' : 'market.schedule.deactivated', $schedule);

        return back()->with('status', 'Créneau '.($schedule->is_active ? 'activé' : 'désactivé').'.');
    }
}

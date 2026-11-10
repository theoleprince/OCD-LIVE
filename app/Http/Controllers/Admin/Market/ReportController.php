<?php

namespace App\Http\Controllers\Admin\Market;

use App\Enums\MarketPermission;
use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\LiveReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        Gate::authorize('market', MarketPermission::View);

        $reports = LiveReport::with('submission.seller')->latest()->paginate(20);

        return view('admin.market.reports.index', compact('reports'));
    }

    public function resolve(LiveReport $report): RedirectResponse
    {
        Gate::authorize('market', MarketPermission::Review);
        $report->update(['status' => 'RESOLVED', 'resolved_at' => now()]);
        AdminActivityLog::record('market.report.resolved', $report->submission);

        return back()->with('status', 'Signalement marqué comme résolu.');
    }
}

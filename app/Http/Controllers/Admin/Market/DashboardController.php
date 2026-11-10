<?php

namespace App\Http\Controllers\Admin\Market;

use App\Enums\LiveSubmissionStatus;
use App\Enums\MarketPermission;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Country;
use App\Models\LiveSubmission;
use App\Models\LiveValidation;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        Gate::authorize('market', MarketPermission::Stats);

        $today = now(config('ocd.timezone'))->toDateString();

        $stats = [
            'total' => LiveSubmission::count(),
            'today' => LiveSubmission::whereDate('submitted_at', $today)->count(),
            'pending' => LiveSubmission::where('status', LiveSubmissionStatus::Pending->value)->count(),
            'under_review' => LiveSubmission::where('status', LiveSubmissionStatus::UnderReview->value)->count(),
            'approved' => LiveSubmission::where('status', LiveSubmissionStatus::Approved->value)->count(),
            'published_now' => LiveSubmission::published()->count(),
            'expired' => LiveSubmission::where('status', LiveSubmissionStatus::Expired->value)->count(),
            'rejected' => LiveSubmission::where('status', LiveSubmissionStatus::Rejected->value)->count(),
        ];

        $byCountry = Country::active()->withCount('submissions')->orderByDesc('submissions_count')->limit(10)->get();
        $byCategory = Category::active()->topLevel()->withCount('submissions')->orderByDesc('submissions_count')->limit(10)->get();
        $byModerator = LiveValidation::query()
            ->selectRaw('moderator_id, count(*) as validations_count')
            ->whereNotNull('moderator_id')
            ->groupBy('moderator_id')
            ->with('moderator')
            ->orderByDesc('validations_count')
            ->get();

        return view('admin.market.dashboard', compact('stats', 'byCountry', 'byCategory', 'byModerator'));
    }
}

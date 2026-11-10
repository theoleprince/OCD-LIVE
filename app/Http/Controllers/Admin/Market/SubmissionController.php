<?php

namespace App\Http\Controllers\Admin\Market;

use App\Enums\LiveSubmissionStatus;
use App\Enums\MarketPermission;
use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Category;
use App\Models\Country;
use App\Models\LiveSubmission;
use App\Services\ModerationWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function __construct(private ModerationWorkflow $workflow) {}

    public function index(Request $request): View
    {
        Gate::authorize('market', MarketPermission::View);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(LiveSubmissionStatus::class)],
            'country' => ['nullable', 'integer'],
            'category' => ['nullable', 'integer'],
        ]);

        $submissions = LiveSubmission::query()
            ->with(['seller', 'country', 'category', 'moderator'])
            ->when($filters['q'] ?? null, function ($q, $term) {
                $q->where(function ($q) use ($term) {
                    $q->where('reference', 'like', "%$term%")
                        ->orWhere('account_name', 'like', "%$term%")
                        ->orWhereHas('seller', fn ($s) => $s->where('full_name', 'like', "%$term%")
                            ->orWhere('business_name', 'like', "%$term%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['country'] ?? null, fn ($q, $v) => $q->where('country_id', $v))
            ->when($filters['category'] ?? null, fn ($q, $v) => $q->where('category_id', $v))
            ->orderByDesc('submitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.market.submissions.index', [
            'submissions' => $submissions,
            'filters' => $filters,
            'countries' => Country::active()->ordered()->get(),
            'categories' => Category::active()->topLevel()->ordered()->get(),
        ]);
    }

    public function show(LiveSubmission $submission): View
    {
        Gate::authorize('market', MarketPermission::View);

        $submission->load(['seller', 'country', 'city', 'category', 'subcategory', 'schedule', 'moderator']);

        $history = AdminActivityLog::with('user')
            ->where('subject_type', $submission->getMorphClass())
            ->where('subject_id', $submission->id)
            ->latest()
            ->get();

        return view('admin.market.submissions.show', compact('submission', 'history'));
    }

    public function hold(Request $request, LiveSubmission $submission): RedirectResponse
    {
        Gate::authorize('market', MarketPermission::Review);
        $data = $request->validate(['comment' => ['nullable', 'string', 'max:2000']]);
        $this->workflow->hold($submission, auth()->user(), $data['comment'] ?? null);

        return back()->with('status', 'Demande mise en attente.');
    }

    public function approve(Request $request, LiveSubmission $submission): RedirectResponse
    {
        Gate::authorize('market', MarketPermission::Approve);
        $data = $request->validate(['comment' => ['nullable', 'string', 'max:2000']]);
        $this->workflow->approve($submission, auth()->user(), $data['comment'] ?? null);

        return back()->with('status', 'Demande approuvée et publiée dans l\'annuaire public.');
    }

    public function reject(Request $request, LiveSubmission $submission): RedirectResponse
    {
        Gate::authorize('market', MarketPermission::Reject);
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);
        $this->workflow->reject($submission, auth()->user(), $data['reason'], $data['comment'] ?? null);

        return back()->with('status', 'Demande refusée.');
    }

    public function cancel(Request $request, LiveSubmission $submission): RedirectResponse
    {
        Gate::authorize('market', MarketPermission::Review);
        $data = $request->validate(['comment' => ['nullable', 'string', 'max:2000']]);
        $this->workflow->cancel($submission, auth()->user(), $data['comment'] ?? null);

        return back()->with('status', 'Demande annulée.');
    }

    /** Commentaire interne, jamais exposé publiquement — modifiable sans changer le statut. */
    public function comment(Request $request, LiveSubmission $submission): RedirectResponse
    {
        Gate::authorize('market', MarketPermission::Review);
        $data = $request->validate(['comment' => ['nullable', 'string', 'max:2000']]);
        $submission->update(['internal_comment' => $data['comment'] ?? null]);
        AdminActivityLog::record('market.comment_updated', $submission);

        return back()->with('status', 'Commentaire interne enregistré.');
    }
}

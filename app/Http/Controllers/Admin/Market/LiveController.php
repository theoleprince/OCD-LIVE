<?php

namespace App\Http\Controllers\Admin\Market;

use App\Enums\MarketPermission;
use App\Http\Controllers\Controller;
use App\Models\LiveSubmission;
use App\Services\ModerationWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LiveController extends Controller
{
    public function __construct(private ModerationWorkflow $workflow) {}

    public function index(): View
    {
        Gate::authorize('market', MarketPermission::View);

        $lives = LiveSubmission::published()
            ->with(['seller', 'country', 'city', 'category', 'moderator'])
            ->orderByDesc('published_at')
            ->paginate(20);

        return view('admin.market.lives.index', compact('lives'));
    }

    public function remove(Request $request, LiveSubmission $submission): RedirectResponse
    {
        Gate::authorize('market', MarketPermission::Remove);
        $data = $request->validate(['comment' => ['nullable', 'string', 'max:2000']]);
        $this->workflow->remove($submission, auth()->user(), $data['comment'] ?? null);

        return back()->with('status', 'Publication retirée.');
    }
}

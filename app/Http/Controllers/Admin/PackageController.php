<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(): View
    {
        return view('admin.packages.index', [
            'packages' => Package::withTrashed()->withCount('bookings')->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.packages.form', ['package' => new Package(['is_active' => true, 'duration_minutes' => 90, 'sort_order' => Package::max('sort_order') + 1])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $package = Package::create($this->validated($request));
        AdminActivityLog::record('package.created', $package, ['name' => $package->name, 'price' => $package->price]);

        return redirect()->route('admin.packages.index')->with('status', 'Package « '.$package->name.' » créé.');
    }

    public function edit(Package $package): View
    {
        return view('admin.packages.form', compact('package'));
    }

    public function update(Request $request, Package $package): RedirectResponse
    {
        $before = $package->only(['price', 'duration_minutes', 'is_active']);
        $package->update($this->validated($request, $package));
        AdminActivityLog::record('package.updated', $package, ['before' => $before, 'after' => $package->only(array_keys($before))]);

        return redirect()->route('admin.packages.index')->with('status', 'Package « '.$package->name.' » mis à jour.');
    }

    public function toggle(Package $package): RedirectResponse
    {
        $package->update(['is_active' => ! $package->is_active]);
        AdminActivityLog::record($package->is_active ? 'package.activated' : 'package.deactivated', $package);

        return back()->with('status', 'Package '.($package->is_active ? 'activé' : 'désactivé').'.');
    }

    public function destroy(Package $package): RedirectResponse
    {
        // Suppression logique : les réservations existantes gardent leur package.
        $package->update(['is_active' => false]);
        $package->delete();
        AdminActivityLog::record('package.deleted', $package);

        return redirect()->route('admin.packages.index')->with('status', 'Package archivé.');
    }

    public function restore(int $id): RedirectResponse
    {
        $package = Package::onlyTrashed()->findOrFail($id);
        $package->restore();
        AdminActivityLog::record('package.restored', $package);

        return back()->with('status', 'Package restauré (inactif). Activez-le pour le publier.');
    }

    private function validated(Request $request, ?Package $package = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:80', 'alpha_dash', Rule::unique('packages', 'slug')->ignore($package?->id)],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'price_validated' => ['boolean'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:720'],
            'duration_label' => ['nullable', 'string', 'max:80'],
            'features_text' => ['nullable', 'string', 'max:5000'],
            'terms' => ['nullable', 'string', 'max:3000'],
            'max_products' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);

        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);
        $data['features'] = collect(preg_split('/\R/', $data['features_text'] ?? ''))->map(fn ($l) => preg_replace('/^[\s\-•]+|[\s\-•]+$/u', '', $l))->filter()->values()->all();
        $data['price_validated'] = $request->boolean('price_validated');
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        unset($data['features_text']);

        return $data;
    }
}

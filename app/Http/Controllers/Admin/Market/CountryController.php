<?php

namespace App\Http\Controllers\Admin\Market;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CountryController extends Controller
{
    public function index(): View
    {
        return view('admin.market.countries.index', [
            'countries' => Country::withTrashed()->withCount(['cities', 'sellers'])->ordered()->with('cities')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'iso_code' => ['required', 'string', 'size:2', 'alpha', 'unique:countries,iso_code'],
            'timezone' => ['required', 'string', 'timezone:all'],
        ]);
        $data['iso_code'] = Str::upper($data['iso_code']);

        $country = Country::create([
            ...$data,
            'slug' => Str::slug($data['name']),
            'flag' => implode('', array_map(fn ($l) => mb_chr(127397 + ord($l)), str_split($data['iso_code']))),
            'is_active' => true,
            'sort_order' => (int) Country::max('sort_order') + 1,
        ]);

        AdminActivityLog::record('market.country.created', $country);

        return back()->with('status', 'Pays « '.$country->name.' » ajouté.');
    }

    public function toggle(Country $country): RedirectResponse
    {
        $country->update(['is_active' => ! $country->is_active]);
        AdminActivityLog::record($country->is_active ? 'market.country.activated' : 'market.country.deactivated', $country);

        return back()->with('status', 'Pays '.($country->is_active ? 'activé' : 'désactivé').'.');
    }

    public function storeCity(Request $request, Country $country): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);

        $city = $country->cities()->firstOrCreate(['name' => $data['name']], ['is_active' => true]);
        AdminActivityLog::record('market.city.created', $city, ['country' => $country->name]);

        return back()->with('status', 'Ville « '.$city->name.' » ajoutée à '.$country->name.'.');
    }

    public function toggleCity(Country $country, City $city): RedirectResponse
    {
        abort_unless($city->country_id === $country->id, 404);
        $city->update(['is_active' => ! $city->is_active]);
        AdminActivityLog::record($city->is_active ? 'market.city.activated' : 'market.city.deactivated', $city);

        return back()->with('status', 'Ville '.($city->is_active ? 'activée' : 'désactivée').'.');
    }
}

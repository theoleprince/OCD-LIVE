<?php

namespace App\Http\Controllers\Admin\Market;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ModeratorController extends Controller
{
    public function index(): View
    {
        return view('admin.market.moderators.index', [
            'moderators' => User::where('role', UserRole::Moderator->value)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
        ]);

        $password = Str::password(20, symbols: false);

        // role/is_active ne sont pas mass-assignables (cf. User::$fillable) : forceFill comme ocd:create-admin.
        $moderator = (new User(['name' => $data['name'], 'email' => $data['email']]))->forceFill([
            'password' => $password,
            'role' => UserRole::Moderator,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $moderator->save();

        AdminActivityLog::record('market.moderator.created', $moderator);

        return back()->with('status', 'Modérateur créé.')->with('generated_password', $password)->with('generated_for', $moderator->email);
    }

    public function toggle(User $user): RedirectResponse
    {
        abort_unless($user->role === UserRole::Moderator, 404);
        $user->update(['is_active' => ! $user->is_active]);
        AdminActivityLog::record($user->is_active ? 'market.moderator.activated' : 'market.moderator.deactivated', $user);

        return back()->with('status', 'Modérateur '.($user->is_active ? 'activé' : 'désactivé').'.');
    }

    public function reset(User $user): RedirectResponse
    {
        abort_unless($user->role === UserRole::Moderator, 404);
        $password = Str::password(20, symbols: false);
        $user->update(['password' => $password]);
        AdminActivityLog::record('market.moderator.password_reset', $user);

        return back()->with('status', 'Mot de passe réinitialisé.')->with('generated_password', $password)->with('generated_for', $user->email);
    }
}

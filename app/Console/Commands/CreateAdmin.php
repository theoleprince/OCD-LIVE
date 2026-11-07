<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateAdmin extends Command
{
    protected $signature = 'ocd:create-admin
        {--email= : Adresse e-mail (sinon ADMIN_EMAIL)}
        {--name= : Nom affiché}
        {--role=admin : admin ou assistant}
        {--reset : Régénère le mot de passe si le compte existe}';

    protected $description = 'Crée (ou réinitialise) un compte de l\'équipe OCD Live. Le mot de passe vient de ADMIN_PASSWORD ou est généré aléatoirement et affiché une seule fois.';

    public function handle(): int
    {
        $email = $this->option('email') ?: config('ocd.admin.email');

        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Indiquez une adresse e-mail valide : --email=... ou variable ADMIN_EMAIL.');

            return self::FAILURE;
        }

        $role = UserRole::tryFrom($this->option('role'));
        if (! $role) {
            $this->error('Rôle invalide (admin ou assistant).');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user && ! $this->option('reset')) {
            $this->info("Le compte $email existe déjà. Utilisez --reset pour régénérer son mot de passe.");

            return self::SUCCESS;
        }

        $fromEnv = (bool) config('ocd.admin.password') && $role === UserRole::Admin && $email === config('ocd.admin.email');
        $password = $fromEnv ? config('ocd.admin.password') : Str::password(20, symbols: false);

        $user ??= new User(['email' => $email]);
        $user->forceFill([
            'name' => $this->option('name') ?: ($user->name ?? config('ocd.admin.name')),
            'password' => $password,
            'role' => $role,
            'is_active' => true,
            'email_verified_at' => now(),
        ])->save();

        $this->info(($user->wasRecentlyCreated ? 'Compte créé' : 'Mot de passe réinitialisé').' : '.$email.' ('.$role->label().')');

        if ($fromEnv) {
            $this->line('Mot de passe : celui défini dans ADMIN_PASSWORD.');
        } else {
            $this->warn('Mot de passe généré (affiché une seule fois, conservez-le en lieu sûr) :');
            $this->line('  '.$password);
        }

        return self::SUCCESS;
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * House of Challenge est le compte TikTok de diffusion, pas l'entreprise :
 * met à jour le libellé de prestation déjà enregistré (données uniquement, rien n'est supprimé).
 */
return new class extends Migration
{
    private const OLD = 'Diffusion sur le compte House of Challenge';
    private const NEW = 'Diffusion en direct sur le compte TikTok House of Challenge';

    public function up(): void
    {
        $this->swap(self::OLD, self::NEW);
    }

    public function down(): void
    {
        $this->swap(self::NEW, self::OLD);
    }

    private function swap(string $from, string $to): void
    {
        DB::table('packages')->whereNotNull('features')->orderBy('id')->each(function ($package) use ($from, $to) {
            $features = json_decode($package->features, true) ?: [];
            $updated = array_map(fn ($f) => $f === $from ? $to : $f, $features);

            if ($updated !== $features) {
                DB::table('packages')->where('id', $package->id)->update(['features' => json_encode($updated, JSON_UNESCAPED_UNICODE)]);
            }
        });
    }
};

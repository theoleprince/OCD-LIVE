<?php

namespace Database\Seeders;

use App\Models\LiveSchedule;
use Illuminate\Database\Seeder;

/** Créneaux de vérification initiaux (cahier des charges §9) — exemples, modifiables depuis l'administration. */
class LiveScheduleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::schedules() as $i => $data) {
            LiveSchedule::firstOrCreate(['name' => $data['name']], [
                ...$data,
                'timezone_policy' => 'country',
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);
        }
    }

    public static function schedules(): array
    {
        return [
            ['name' => 'Matin', 'start_time' => '08:00', 'end_time' => '11:00'],
            ['name' => 'Après-midi', 'start_time' => '12:00', 'end_time' => '15:00'],
            ['name' => 'Soir', 'start_time' => '17:00', 'end_time' => '20:00'],
        ];
    }
}

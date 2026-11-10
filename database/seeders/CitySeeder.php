<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Seeder;

/**
 * Villes de démarrage (capitale + une ville commerciale majeure quand pertinent) pour chaque pays.
 * Liste non exhaustive : l'administrateur peut ajouter d'autres villes depuis /admin/market/countries.
 */
class CitySeeder extends Seeder
{
    public function run(): void
    {
        $countries = Country::query()->pluck('id', 'iso_code');

        foreach (self::cities() as $isoCode => $names) {
            $countryId = $countries[$isoCode] ?? null;

            if (! $countryId) {
                continue;
            }

            foreach ($names as $name) {
                City::firstOrCreate(['country_id' => $countryId, 'name' => $name], ['is_active' => true]);
            }
        }
    }

    public static function cities(): array
    {
        return [
            'DZ' => ['Alger', 'Oran'],
            'AO' => ['Luanda'],
            'BJ' => ['Cotonou', 'Porto-Novo'],
            'BW' => ['Gaborone'],
            'BF' => ['Ouagadougou', 'Bobo-Dioulasso'],
            'BI' => ['Bujumbura', 'Gitega'],
            'CV' => ['Praia'],
            'CM' => ['Douala', 'Yaoundé'],
            'CF' => ['Bangui'],
            'TD' => ['N\'Djaména'],
            'KM' => ['Moroni'],
            'CG' => ['Brazzaville', 'Pointe-Noire'],
            'CD' => ['Kinshasa', 'Lubumbashi'],
            'CI' => ['Abidjan', 'Yamoussoukro'],
            'DJ' => ['Djibouti'],
            'EG' => ['Le Caire', 'Alexandrie'],
            'GQ' => ['Malabo'],
            'ER' => ['Asmara'],
            'SZ' => ['Mbabane'],
            'ET' => ['Addis-Abeba'],
            'GA' => ['Libreville'],
            'GM' => ['Banjul'],
            'GH' => ['Accra', 'Kumasi'],
            'GN' => ['Conakry'],
            'GW' => ['Bissau'],
            'KE' => ['Nairobi', 'Mombasa'],
            'LS' => ['Maseru'],
            'LR' => ['Monrovia'],
            'LY' => ['Tripoli'],
            'MG' => ['Antananarivo'],
            'MW' => ['Lilongwe'],
            'ML' => ['Bamako'],
            'MR' => ['Nouakchott'],
            'MU' => ['Port-Louis'],
            'MA' => ['Casablanca', 'Rabat'],
            'MZ' => ['Maputo'],
            'NA' => ['Windhoek'],
            'NE' => ['Niamey'],
            'NG' => ['Lagos', 'Abuja'],
            'RW' => ['Kigali'],
            'ST' => ['São Tomé'],
            'SN' => ['Dakar', 'Touba'],
            'SC' => ['Victoria'],
            'SL' => ['Freetown'],
            'SO' => ['Mogadiscio'],
            'ZA' => ['Johannesburg', 'Le Cap', 'Pretoria'],
            'SS' => ['Djouba'],
            'SD' => ['Khartoum'],
            'TZ' => ['Dar es Salaam', 'Dodoma'],
            'TG' => ['Lomé', 'Kara'],
            'TN' => ['Tunis', 'Sfax'],
            'UG' => ['Kampala'],
            'ZM' => ['Lusaka'],
            'ZW' => ['Harare', 'Bulawayo'],
        ];
    }
}

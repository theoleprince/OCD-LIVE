<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Les 54 États membres de l'Union Africaine. Idempotent (firstOrCreate par iso_code) :
 * n'écrase jamais un pays déjà présent (les modifications faites dans l'administration sont conservées).
 */
class CountrySeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::countries() as $i => $data) {
            Country::withTrashed()->firstOrCreate(['iso_code' => $data['iso_code']], [
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'flag' => self::flagEmoji($data['iso_code']),
                'timezone' => $data['timezone'],
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);
        }
    }

    /** Emoji drapeau calculé à partir du code ISO (deux symboles indicateurs régionaux Unicode). */
    public static function flagEmoji(string $isoCode): string
    {
        return implode('', array_map(
            fn (string $letter) => mb_chr(127397 + ord($letter)),
            str_split(strtoupper($isoCode))
        ));
    }

    public static function countries(): array
    {
        return [
            ['iso_code' => 'DZ', 'name' => 'Algérie', 'timezone' => 'Africa/Algiers'],
            ['iso_code' => 'AO', 'name' => 'Angola', 'timezone' => 'Africa/Luanda'],
            ['iso_code' => 'BJ', 'name' => 'Bénin', 'timezone' => 'Africa/Porto-Novo'],
            ['iso_code' => 'BW', 'name' => 'Botswana', 'timezone' => 'Africa/Gaborone'],
            ['iso_code' => 'BF', 'name' => 'Burkina Faso', 'timezone' => 'Africa/Ouagadougou'],
            ['iso_code' => 'BI', 'name' => 'Burundi', 'timezone' => 'Africa/Bujumbura'],
            ['iso_code' => 'CV', 'name' => 'Cap-Vert', 'timezone' => 'Atlantic/Cape_Verde'],
            ['iso_code' => 'CM', 'name' => 'Cameroun', 'timezone' => 'Africa/Douala'],
            ['iso_code' => 'CF', 'name' => 'République centrafricaine', 'timezone' => 'Africa/Bangui'],
            ['iso_code' => 'TD', 'name' => 'Tchad', 'timezone' => 'Africa/Ndjamena'],
            ['iso_code' => 'KM', 'name' => 'Comores', 'timezone' => 'Indian/Comoro'],
            ['iso_code' => 'CG', 'name' => 'Congo', 'timezone' => 'Africa/Brazzaville'],
            ['iso_code' => 'CD', 'name' => 'République démocratique du Congo', 'timezone' => 'Africa/Kinshasa'],
            ['iso_code' => 'CI', 'name' => 'Côte d\'Ivoire', 'timezone' => 'Africa/Abidjan'],
            ['iso_code' => 'DJ', 'name' => 'Djibouti', 'timezone' => 'Africa/Djibouti'],
            ['iso_code' => 'EG', 'name' => 'Égypte', 'timezone' => 'Africa/Cairo'],
            ['iso_code' => 'GQ', 'name' => 'Guinée équatoriale', 'timezone' => 'Africa/Malabo'],
            ['iso_code' => 'ER', 'name' => 'Érythrée', 'timezone' => 'Africa/Asmara'],
            ['iso_code' => 'SZ', 'name' => 'Eswatini', 'timezone' => 'Africa/Mbabane'],
            ['iso_code' => 'ET', 'name' => 'Éthiopie', 'timezone' => 'Africa/Addis_Ababa'],
            ['iso_code' => 'GA', 'name' => 'Gabon', 'timezone' => 'Africa/Libreville'],
            ['iso_code' => 'GM', 'name' => 'Gambie', 'timezone' => 'Africa/Banjul'],
            ['iso_code' => 'GH', 'name' => 'Ghana', 'timezone' => 'Africa/Accra'],
            ['iso_code' => 'GN', 'name' => 'Guinée', 'timezone' => 'Africa/Conakry'],
            ['iso_code' => 'GW', 'name' => 'Guinée-Bissau', 'timezone' => 'Africa/Bissau'],
            ['iso_code' => 'KE', 'name' => 'Kenya', 'timezone' => 'Africa/Nairobi'],
            ['iso_code' => 'LS', 'name' => 'Lesotho', 'timezone' => 'Africa/Maseru'],
            ['iso_code' => 'LR', 'name' => 'Liberia', 'timezone' => 'Africa/Monrovia'],
            ['iso_code' => 'LY', 'name' => 'Libye', 'timezone' => 'Africa/Tripoli'],
            ['iso_code' => 'MG', 'name' => 'Madagascar', 'timezone' => 'Indian/Antananarivo'],
            ['iso_code' => 'MW', 'name' => 'Malawi', 'timezone' => 'Africa/Blantyre'],
            ['iso_code' => 'ML', 'name' => 'Mali', 'timezone' => 'Africa/Bamako'],
            ['iso_code' => 'MR', 'name' => 'Mauritanie', 'timezone' => 'Africa/Nouakchott'],
            ['iso_code' => 'MU', 'name' => 'Maurice', 'timezone' => 'Indian/Mauritius'],
            ['iso_code' => 'MA', 'name' => 'Maroc', 'timezone' => 'Africa/Casablanca'],
            ['iso_code' => 'MZ', 'name' => 'Mozambique', 'timezone' => 'Africa/Maputo'],
            ['iso_code' => 'NA', 'name' => 'Namibie', 'timezone' => 'Africa/Windhoek'],
            ['iso_code' => 'NE', 'name' => 'Niger', 'timezone' => 'Africa/Niamey'],
            ['iso_code' => 'NG', 'name' => 'Nigeria', 'timezone' => 'Africa/Lagos'],
            ['iso_code' => 'RW', 'name' => 'Rwanda', 'timezone' => 'Africa/Kigali'],
            ['iso_code' => 'ST', 'name' => 'Sao Tomé-et-Principe', 'timezone' => 'Africa/Sao_Tome'],
            ['iso_code' => 'SN', 'name' => 'Sénégal', 'timezone' => 'Africa/Dakar'],
            ['iso_code' => 'SC', 'name' => 'Seychelles', 'timezone' => 'Indian/Mahe'],
            ['iso_code' => 'SL', 'name' => 'Sierra Leone', 'timezone' => 'Africa/Freetown'],
            ['iso_code' => 'SO', 'name' => 'Somalie', 'timezone' => 'Africa/Mogadishu'],
            ['iso_code' => 'ZA', 'name' => 'Afrique du Sud', 'timezone' => 'Africa/Johannesburg'],
            ['iso_code' => 'SS', 'name' => 'Soudan du Sud', 'timezone' => 'Africa/Juba'],
            ['iso_code' => 'SD', 'name' => 'Soudan', 'timezone' => 'Africa/Khartoum'],
            ['iso_code' => 'TZ', 'name' => 'Tanzanie', 'timezone' => 'Africa/Dar_es_Salaam'],
            ['iso_code' => 'TG', 'name' => 'Togo', 'timezone' => 'Africa/Lome'],
            ['iso_code' => 'TN', 'name' => 'Tunisie', 'timezone' => 'Africa/Tunis'],
            ['iso_code' => 'UG', 'name' => 'Ouganda', 'timezone' => 'Africa/Kampala'],
            ['iso_code' => 'ZM', 'name' => 'Zambie', 'timezone' => 'Africa/Lusaka'],
            ['iso_code' => 'ZW', 'name' => 'Zimbabwe', 'timezone' => 'Africa/Harare'],
        ];
    }
}

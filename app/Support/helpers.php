<?php

use App\Models\Setting;
use Carbon\CarbonInterface;

if (! function_exists('money_fcfa')) {
    function money_fcfa(int|float|null $amount): string
    {
        return number_format((float) $amount, 0, ',', ' ').' FCFA';
    }
}

if (! function_exists('duration_text')) {
    function duration_text(?int $minutes): string
    {
        if (! $minutes) {
            return '—';
        }

        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        if ($h === 0) {
            return $m.' min';
        }

        return $m ? sprintf('%d h %02d', $h, $m) : $h.' h';
    }
}

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('date_fr')) {
    function date_fr(CarbonInterface $date, string $format = 'l j F Y'): string
    {
        return ucfirst($date->locale('fr')->translatedFormat($format));
    }
}

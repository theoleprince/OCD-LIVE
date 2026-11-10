<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Package;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'packages' => Package::active()->ordered()->get(),
        ]);
    }

    public function booking(): View
    {
        return view('public.booking');
    }

    public function terms(): View
    {
        return view('public.legal', ['title' => 'Conditions générales', 'page' => 'terms']);
    }

    public function privacy(): View
    {
        return view('public.legal', ['title' => 'Politique de confidentialité', 'page' => 'privacy']);
    }

    public function sitemap(): Response
    {
        $urls = [
            ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => route('booking'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('legal.terms'), 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => route('legal.privacy'), 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => route('market.index'), 'priority' => '0.9', 'changefreq' => 'hourly'],
            ['loc' => route('market.submit'), 'priority' => '0.6', 'changefreq' => 'monthly'],
        ];

        foreach (Country::active()->get() as $country) {
            $urls[] = ['loc' => route('market.country', $country), 'priority' => '0.7', 'changefreq' => 'daily'];
        }

        return response()->view('public.sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $lines = app()->isProduction()
            ? ['User-agent: *', 'Disallow: /admin', 'Disallow: /connexion', 'Allow: /', '', 'Sitemap: '.route('sitemap')]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n")->header('Content-Type', 'text/plain');
    }
}

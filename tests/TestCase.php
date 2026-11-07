<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Les tests ne dépendent pas du build front (npm run build).
        $this->withoutVite();

        // Le cache mémoire des paramètres ne doit pas fuiter d'un test à l'autre.
        \App\Models\Setting::flush();
    }
}

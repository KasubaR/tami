<?php

namespace Tests;

use App\Http\Middleware\EnsureGameAccess;
use App\Models\BirthdayGame;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Tests should not depend on compiled front-end assets.
        $this->withoutVite();
    }

    /** Act as a browser that has opened the private link. */
    protected function grantAccess(): static
    {
        return $this->withSession([
            'game_access' => EnsureGameAccess::fingerprint(BirthdayGame::firstOrFail()->access_token),
        ]);
    }
}

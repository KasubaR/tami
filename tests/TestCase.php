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

        // Most tests play the game, so it is open by default; schedule tests override this.
        config(['game.opens_at' => '2000-01-01 00:00']);
    }

    /** Act as a browser that has opened the private link. */
    protected function grantAccess(): static
    {
        return $this->withSession([
            'game_access' => EnsureGameAccess::fingerprint(BirthdayGame::firstOrFail()->access_token),
        ]);
    }
}

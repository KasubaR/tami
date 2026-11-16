<?php

namespace Tests\Feature;

use App\Models\GameLevel;
use App\Models\GameProgress;
use App\Services\CrosswordPuzzle;
use Database\Seeders\BirthdayGameSeeder;
use Database\Seeders\CrosswordWordSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardProgressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([BirthdayGameSeeder::class, CrosswordWordSeeder::class]);
    }

    private function solve(int $n)
    {
        $cells = [];
        foreach (GameLevel::where('level_number', $n)->firstOrFail()->words as $w) {
            foreach (str_split($w->answer) as $i => $ch) {
                $r = $w->start_row + ($w->direction === 'down' ? $i : 0);
                $c = $w->start_col + ($w->direction === 'across' ? $i : 0);
                $cells["$r,$c"] = $ch;
            }
        }

        return $this->postJson("/play/$n/check", ['cells' => $cells]);
    }

    public function test_new_game_shows_title_name_and_starts_at_level_one(): void
    {
        $this->get('/play')
            ->assertOk()
            ->assertSee('The Birthday Mystery')
            ->assertSee('Tamara')
            ->assertSee('0</span> of 11 gifts unlocked', false)
            ->assertSee('Start playing')
            ->assertSee('href="'.url('/play/1').'"', false);
    }

    public function test_dashboard_shows_all_eleven_levels(): void
    {
        $html = $this->get('/play')->getContent();
        foreach (range(1, 11) as $n) {
            $this->assertStringContainsString("Puzzle $n", $html);
        }
    }

    public function test_levels_unlock_one_at_a_time_in_order(): void
    {
        // Cannot jump ahead.
        foreach ([2, 3, 11] as $n) {
            $this->get("/play/$n")->assertRedirect('/play');
            $this->solve($n)->assertForbidden();
            $this->get("/play/$n/gift")->assertRedirect('/play');
        }

        $this->solve(1)->assertJsonPath('solved', true);

        // Level 2 is now reachable; level 3 still is not.
        $this->get('/play/2')->assertOk();
        $this->get('/play/3')->assertRedirect('/play');
        $this->solve(3)->assertForbidden();

        $this->get('/play')
            ->assertSee('1</span> of 11 gifts unlocked', false)
            ->assertSee('Continue playing · Puzzle 2')
            ->assertSee('The First Surprise')   // gift 1 now revealed
            ->assertDontSee('A Little Beauty'); // gift 2 still hidden
    }

    public function test_replaying_a_completed_level_goes_to_its_gift(): void
    {
        $this->solve(1);
        $this->get('/play/1')->assertRedirect('/play/1/gift');
    }

    public function test_finishing_all_levels_unlocks_the_finale(): void
    {
        $this->get('/finale')->assertRedirect('/play');

        foreach (range(1, 11) as $n) {
            $this->solve($n)->assertJsonPath('solved', true);
        }

        $this->assertCount(11, GameProgress::first()->revealed_gifts);
        $this->get('/play')->assertSee('Open the final surprise')->assertSee('href="'.url('/finale').'"', false);
        $this->get('/finale')->assertOk()->assertSee('Happy Birthday, Tamara!');
        $this->get('/play/11/gift')->assertOk()->assertSee('The Final Surprise')->assertSee('/finale', false);
    }
}

<?php

namespace Tests\Feature;

use App\Models\GameLevel;
use App\Models\GameProgress;
use App\Models\Gift;
use Database\Seeders\BirthdayGameSeeder;
use Database\Seeders\CrosswordWordSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevealAndFinaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([BirthdayGameSeeder::class, CrosswordWordSeeder::class]);
        $this->grantAccess();
    }

    private function solve(int $n)
    {
        $cells = [];
        foreach (GameLevel::where('level_number', $n)->firstOrFail()->words as $w) {
            foreach (str_split($w->answer) as $i => $ch) {
                $cells[($w->start_row + ($w->direction === 'down' ? $i : 0)).','.($w->start_col + ($w->direction === 'across' ? $i : 0))] = $ch;
            }
        }

        return $this->postJson("/play/$n/check", ['cells' => $cells]);
    }

    public function test_every_gift_has_a_personal_message_and_emoji(): void
    {
        $this->assertSame(11, Gift::count());
        foreach (Gift::all() as $gift) {
            $this->assertNotEmpty($gift->emoji, "{$gift->name} has no emoji");
            $this->assertGreaterThan(30, strlen($gift->reveal_message), "{$gift->name} message too short");
        }
    }

    public function test_gift_reveal_has_the_full_sequence(): void
    {
        $this->solve(1);

        $this->get('/play/1/gift')
            ->assertOk()
            ->assertSee('You did it, Tamara!')
            ->assertSee('id="gift-box"', false)     // closed box to tap
            ->assertSee('The First Surprise')       // heading
            ->assertSee('Shower pack')              // gift name
            ->assertSee(Gift::where('name', 'Shower pack')->value('reveal_message'))
            ->assertSee('Next challenge')
            ->assertSee('/play/2', false);
    }

    public function test_every_level_has_its_own_reveal_after_completion(): void
    {
        foreach (range(1, 11) as $n) {
            $this->solve($n)->assertJsonPath('solved', true);
            $gift = GameLevel::where('level_number', $n)->first()->gift;

            $this->get("/play/$n/gift")->assertOk()->assertSee($gift->name)->assertSee($gift->reveal_heading);
        }
    }

    public function test_replaying_or_resubmitting_creates_no_duplicate_progress(): void
    {
        $this->solve(1);
        $this->solve(1);
        $this->get('/play/1/gift')->assertOk();
        $this->get('/play/1/gift')->assertOk();

        $this->assertSame(1, GameProgress::count());
        $progress = GameProgress::first();
        $this->assertSame([1], $progress->completed_levels);
        $this->assertCount(1, $progress->revealed_gifts);
    }

    public function test_welcome_page_is_personal(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Happy Birthday,', false)
            ->assertSee('Tamara')
            ->assertSee('Eleven puzzles')
            ->assertSee('Start My Adventure')
            ->assertSee('aria-label="Music player"', false)
            ->assertSee('id="m-play"', false);

        $this->solve(1);
        $this->get('/')->assertSee('Continue My Adventure');
    }

    public function test_finale_shows_all_eleven_gifts_and_messages_only_when_complete(): void
    {
        $this->get('/finale')->assertRedirect('/play');

        foreach (range(1, 11) as $n) {
            $this->solve($n);
        }

        $response = $this->get('/finale')->assertOk()
            ->assertSee('Your eleven gifts')
            ->assertSee(config('game.closing'));

        foreach (Gift::all() as $gift) {
            $response->assertSee($gift->name);
        }
    }
}

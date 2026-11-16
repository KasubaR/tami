<?php

namespace Tests\Feature;

use App\Models\BirthdayGame;
use App\Models\GameLevel;
use App\Models\GameProgress;
use App\Services\CrosswordPuzzle;
use Database\Seeders\BirthdayGameSeeder;
use Database\Seeders\CrosswordWordSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityAndPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([BirthdayGameSeeder::class, CrosswordWordSeeder::class]);
    }

    private function token(): string
    {
        return BirthdayGame::first()->access_token;
    }

    private function cellsFor(int $n, ?int $onlyWordIndex = null): array
    {
        $cells = [];
        $words = GameLevel::where('level_number', $n)->firstOrFail()->words->values();
        foreach ($words as $idx => $w) {
            if ($onlyWordIndex !== null && $idx !== $onlyWordIndex) {
                continue;
            }
            foreach (str_split($w->answer) as $i => $ch) {
                $cells[($w->start_row + ($w->direction === 'down' ? $i : 0)).','.($w->start_col + ($w->direction === 'across' ? $i : 0))] = $ch;
            }
        }

        return $cells;
    }

    // ---- Private access -------------------------------------------------

    public function test_token_is_long_random_and_unique_per_game(): void
    {
        $this->assertGreaterThanOrEqual(32, strlen($this->token()));
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]+$/', $this->token());
    }

    public function test_every_page_is_hidden_without_the_private_link(): void
    {
        foreach (['/', '/play', '/play/1', '/play/1/gift', '/finale'] as $url) {
            $this->get($url)->assertNotFound();
        }
        $this->postJson('/play/1/check', ['cells' => $this->cellsFor(1)])->assertNotFound();
        $this->assertSame([], GameProgress::first()->completed_levels);
    }

    public function test_wrong_token_is_rejected_and_right_token_grants_access(): void
    {
        $this->get('/g/'.str_repeat('a', 40))->assertNotFound();
        $this->get('/play')->assertNotFound();

        $this->get('/g/'.$this->token())->assertRedirect('/');
        $this->get('/')->assertOk()->assertSee('Happy Birthday,', false);
        $this->get('/play')->assertOk();
    }

    public function test_rotating_the_token_locks_out_old_sessions(): void
    {
        $this->get('/g/'.$this->token());
        $this->get('/play')->assertOk();

        $this->artisan('game:link', ['--rotate' => true])->assertSuccessful();

        $this->get('/play')->assertNotFound();
    }

    public function test_link_command_prints_the_private_url(): void
    {
        $this->artisan('game:link')->expectsOutputToContain('/g/'.$this->token())->assertSuccessful();
    }

    public function test_responses_carry_privacy_headers(): void
    {
        $response = $this->grantAccess()->get('/play');
        $this->assertStringContainsString('noindex', $response->headers->get('X-Robots-Tag'));
        $response->assertHeader('Referrer-Policy', 'no-referrer');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_only_the_expected_routes_exist(): void
    {
        $allowed = ['g/{token}', '/', 'play', 'play/{number}', 'play/{number}/check', 'play/{number}/gift', 'finale', 'up', 'storage/{path}'];

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri() === '' ? '/' : $route->uri();
            $this->assertContains($uri, $allowed, "Unexpected route: $uri");
        }
    }

    // ---- Level protection ----------------------------------------------

    public function test_manipulated_level_numbers_are_refused(): void
    {
        $this->grantAccess();

        foreach ([0, 12, 999] as $n) {
            $this->get("/play/$n")->assertNotFound();
            $this->postJson("/play/$n/check", ['cells' => ['0,0' => 'A']])->assertNotFound();
            $this->get("/play/$n/gift")->assertNotFound();
        }
        $this->get('/play/abc')->assertNotFound();
        $this->get('/play/-1')->assertNotFound();

        // Real but unreached levels redirect or forbid; nothing unlocks.
        $this->get('/play/5')->assertRedirect('/play');
        $this->postJson('/play/5/check', ['cells' => $this->cellsFor(5)])->assertForbidden();
        $this->assertSame([], GameProgress::first()->completed_levels);
    }

    public function test_answer_submission_is_validated_and_rate_limited(): void
    {
        $this->grantAccess();

        $this->postJson('/play/1/check', [])->assertStatus(422);
        $this->postJson('/play/1/check', ['cells' => 'nope'])->assertStatus(422);

        $limited = false;
        for ($i = 0; $i < 40; $i++) {
            if ($this->postJson('/play/1/check', ['cells' => ['0,0' => 'X']])->status() === 429) {
                $limited = true;
                break;
            }
        }
        $this->assertTrue($limited, 'Answer checks should be rate limited');
    }

    // ---- Saving and restoring progress ---------------------------------

    public function test_each_solved_word_is_saved_and_restored(): void
    {
        $this->grantAccess();
        $level = GameLevel::where('level_number', 1)->first();
        $first = $level->words->values()->first();

        // Solve just one word.
        $this->postJson('/play/1/check', ['cells' => $this->cellsFor(1, 0)])
            ->assertOk()->assertJsonPath('solved', false);

        $this->assertSame([$first->id], GameProgress::first()->solvedWordsFor(1));

        // Returning to the puzzle: the solved word is sent filled in, nothing else is.
        $html = $this->get('/play/1')->getContent();
        $this->assertStringContainsString('"solved":{"'.$first->id.'":"'.$first->answer.'"}', $html);
        foreach ($level->words->skip(1) as $other) {
            $this->assertStringNotContainsString('"'.$other->id.'":"'.$other->answer.'"', $html);
        }
    }

    public function test_saved_words_count_even_if_the_browser_forgets_them(): void
    {
        $this->grantAccess();
        $all = $this->cellsFor(1);
        $firstWordCells = $this->cellsFor(1, 0);

        $this->postJson('/play/1/check', ['cells' => $firstWordCells]);

        // Submit everything except the letters unique to word 0: it is still saved as solved.
        $words = GameLevel::where('level_number', 1)->first()->words->values();
        $withoutFirst = [];
        foreach ($words->skip(1) as $i => $w) {
            foreach (str_split($w->answer) as $j => $ch) {
                $withoutFirst[($w->start_row + ($w->direction === 'down' ? $j : 0)).','.($w->start_col + ($w->direction === 'across' ? $j : 0))] = $ch;
            }
        }
        // Remove cells that only belong to word 0.
        foreach (array_diff_key($firstWordCells, $withoutFirst) as $k => $_) {
            unset($withoutFirst[$k]);
        }

        $this->postJson('/play/1/check', ['cells' => $withoutFirst])
            ->assertOk()->assertJsonPath('solved', true);
    }

    public function test_progress_survives_a_new_session_via_the_private_link(): void
    {
        $this->get('/g/'.$this->token());
        $this->postJson('/play/1/check', ['cells' => $this->cellsFor(1)])->assertJsonPath('solved', true);

        // A completely fresh browser (no cookies) opens the same link later.
        $this->flushSession();
        $this->get('/play')->assertNotFound();
        $this->get('/g/'.$this->token());

        $this->get('/play')
            ->assertOk()
            ->assertSee('The First Surprise')
            ->assertSee('Continue playing · Puzzle 2');
        $this->get('/play/1/gift')->assertOk()->assertSee('Cake');
    }

    public function test_duplicate_unlocks_are_prevented(): void
    {
        $this->grantAccess();

        foreach (range(1, 3) as $_) {
            $this->postJson('/play/1/check', ['cells' => $this->cellsFor(1)])->assertJsonPath('solved', true);
        }

        $progress = GameProgress::first();
        $this->assertSame([1], $progress->completed_levels);
        $this->assertCount(1, $progress->revealed_gifts);
        $this->assertSame(1, GameProgress::count());
    }
}

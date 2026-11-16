<?php

namespace Tests\Feature;

use App\Models\BirthdayGame;
use App\Models\GameLevel;
use App\Models\GameProgress;
use App\Services\CrosswordPuzzle;
use Database\Seeders\BirthdayGameSeeder;
use Database\Seeders\CrosswordWordSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrosswordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([BirthdayGameSeeder::class, CrosswordWordSeeder::class]);
    }

    private function level(int $n): GameLevel
    {
        return GameLevel::where('level_number', $n)->firstOrFail();
    }

    /** Correct letters ("row,col" => letter) for a level, read from its words. */
    private function solution(int $level = 1): array
    {
        $cells = [];
        foreach ($this->level($level)->words as $w) {
            foreach (str_split($w->answer) as $i => $ch) {
                $r = $w->start_row + ($w->direction === 'down' ? $i : 0);
                $c = $w->start_col + ($w->direction === 'across' ? $i : 0);
                $cells["$r,$c"] = $ch;
            }
        }

        return $cells;
    }

    public function test_normalize_uppercases_and_strips_spaces(): void
    {
        $this->assertSame('POWERBANK', CrosswordPuzzle::normalize(' power  bank '));
        $this->assertSame('ABC', CrosswordPuzzle::normalize("a-b\tc"));
    }

    public function test_payload_numbers_cells_in_reading_order_and_hides_answers(): void
    {
        $payload = CrosswordPuzzle::payload($this->level(1));

        $this->assertSame(6, $payload['rows']);
        $this->assertSame(10, $payload['cols']);

        // Words sharing a start cell share a number; numbers follow reading order.
        $byStart = [];
        foreach ($payload['words'] as $w) {
            $byStart[$w['row'].','.$w['col']][] = $w['number'];
        }
        foreach ($byStart as $numbers) {
            $this->assertCount(1, array_unique($numbers));
        }
        $ordered = collect($payload['words'])->sortBy(['row', 'col'])->pluck('number')->unique()->values()->all();
        $this->assertSame(range(1, count($ordered)), $ordered);

        $json = json_encode($payload);
        $this->assertStringNotContainsString('SHOWERPACK', $json);
        $this->assertStringNotContainsString('"answer"', $json);
    }

    public function test_crossword_page_does_not_leak_answers_or_gifts(): void
    {
        $html = $this->get('/play/1')->assertOk()->getContent();

        $this->assertStringNotContainsString('SHOWERPACK', $html);
        $this->assertStringNotContainsString('Shower pack', $html);
        $this->assertStringNotContainsString('The First Surprise', $html);
    }

    public function test_dashboard_hides_gifts_until_completed(): void
    {
        $this->get('/play')->assertOk()->assertDontSee('Shower pack')->assertDontSee('The First Surprise');
    }

    public function test_wrong_answers_do_not_unlock_gift(): void
    {
        $cells = array_merge($this->solution(), ['1,0' => 'X']);

        $this->postJson('/play/1/check', ['cells' => $cells])
            ->assertOk()
            ->assertJsonPath('solved', false);

        $this->assertSame([], GameProgress::first()->completed_levels);
        $this->get('/play/1/gift')->assertRedirect('/play');
    }

    public function test_correct_answers_complete_level_and_reveal_gift(): void
    {
        $cells = array_map('strtolower', $this->solution()); // case-insensitive

        $this->postJson('/play/1/check', ['cells' => $cells])
            ->assertOk()
            ->assertJsonPath('solved', true)
            ->assertJsonPath('redirect', url('/play/1/gift'));

        $this->assertSame([1], GameProgress::first()->completed_levels);
        $this->get('/play/1/gift')->assertOk()->assertSee('Shower pack')->assertSee('The First Surprise');
    }

    public function test_locked_levels_cannot_be_submitted(): void
    {
        $this->postJson('/play/2/check', ['cells' => $this->solution()])->assertForbidden();
        $this->get('/play/2')->assertRedirect('/play');
        $this->assertSame([], GameProgress::first()->completed_levels);
    }

    public function test_finale_requires_every_level(): void
    {
        $this->get('/finale')->assertRedirect('/play');

        $game = BirthdayGame::first();
        $game->progress->update(['completed_levels' => range(1, 11)]);

        $this->get('/finale')->assertOk()->assertSee($game->birthday_message);
    }
}

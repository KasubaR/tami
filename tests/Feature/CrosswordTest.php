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

    /** Letters for the seeded level-1 puzzle. */
    private function solution(): array
    {
        return [
            '0,0' => 'P', '0,1' => 'A', '0,2' => 'R', '0,3' => 'T', '0,4' => 'Y',
            '1,0' => 'I', '1,2' => 'I', '1,4' => 'E',
            '2,0' => 'N', '2,1' => 'U', '2,2' => 'N', '2,4' => 'A',
            '3,0' => 'K', '3,2' => 'G', '3,4' => 'R',
        ];
    }

    public function test_normalize_uppercases_and_strips_spaces(): void
    {
        $this->assertSame('POWERBANK', CrosswordPuzzle::normalize(' power  bank '));
        $this->assertSame('ABC', CrosswordPuzzle::normalize("a-b\tc"));
    }

    public function test_payload_numbers_cells_and_hides_answers(): void
    {
        $payload = CrosswordPuzzle::payload($this->level(1));

        $this->assertSame(4, $payload['rows']);
        $this->assertSame(5, $payload['cols']);

        $numbers = collect($payload['words'])->mapWithKeys(fn ($w) => [$w['direction'].$w['row'].$w['col'] => $w['number']]);
        $this->assertSame(1, $numbers['across00']);
        $this->assertSame(1, $numbers['down00']); // shares a start cell
        $this->assertSame(2, $numbers['down02']);
        $this->assertSame(3, $numbers['down04']);
        $this->assertSame(4, $numbers['across20']);

        $this->assertStringNotContainsString('PARTY', json_encode($payload));
    }

    public function test_crossword_page_does_not_leak_answers_or_gifts(): void
    {
        $html = $this->get('/play/1')->assertOk()->getContent();

        $this->assertStringNotContainsString('PARTY', $html);
        $this->assertStringNotContainsString('Shower pack', $html);
        $this->assertStringNotContainsString('The First Surprise', $html);
    }

    public function test_dashboard_hides_gifts_until_completed(): void
    {
        $this->get('/play')->assertOk()->assertDontSee('Shower pack')->assertDontSee('The First Surprise');
    }

    public function test_wrong_answers_do_not_unlock_gift(): void
    {
        $cells = array_merge($this->solution(), ['0,0' => 'X']);

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

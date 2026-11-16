<?php

namespace Tests\Feature;

use App\Models\GameProgress;
use App\Support\GameSchedule;
use Database\Seeders\BirthdayGameSeeder;
use Database\Seeders\CrosswordWordSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OpeningTimeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([BirthdayGameSeeder::class, CrosswordWordSeeder::class]);
        $this->grantAccess();

        // The real schedule: opens 20 Nov 2026 at 14:00 Zambian time.
        config(['game.opens_at' => '2026-11-20 14:00']);
    }

    public function test_opening_time_is_14_00_zambian_time(): void
    {
        $opens = GameSchedule::opensAt();

        $this->assertSame('Africa/Lusaka', $opens->timezoneName);
        $this->assertSame('2026-11-20 12:00:00', $opens->copy()->utc()->format('Y-m-d H:i:s')); // UTC+2
    }

    public function test_before_opening_the_welcome_page_is_locked_with_a_hint(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-20 13:59:00', 'Africa/Lusaka'));

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Zambian time')
            ->assertDontSee('Opens 20 November')
            ->assertSee('A LITTLE HINT')
            ->assertSee('COUNTING DOWN TO YOUR BIRTHDAY')
            ->assertDontSee('Start My Adventure')
            ->assertDontSee('href="'.url('/play').'"', false);
    }

    public function test_countdown_runs_to_midnight_but_the_game_opens_at_14_00(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-18 10:00:00', 'Africa/Lusaka'));

        $this->get('/')
            ->assertOk()
            ->assertSee('data-target="2026-11-20T00:00:00+02:00"', false)
            ->assertSee('data-opens="2026-11-20T14:00:00+02:00"', false);
    }

    public function test_birthday_banner_replaces_the_countdown_at_midnight(): void
    {
        // Just before midnight: the countdown shows and the banner is hidden.
        Carbon::setTestNow(Carbon::parse('2026-11-19 23:59:59', 'Africa/Lusaka'));
        $this->get('/')
            ->assertSee('id="birthday-banner" hidden', false)
            ->assertDontSee('id="countdown-block" hidden', false);

        // At midnight: the banner shows "Happy 27th Birthday" and the countdown is hidden.
        Carbon::setTestNow(Carbon::parse('2026-11-20 00:00:00', 'Africa/Lusaka'));
        $this->get('/')
            ->assertDontSee('id="birthday-banner" hidden', false)
            ->assertSee('id="countdown-block" hidden', false)
            ->assertSee('Happy 27th Birthday');
    }

    public function test_before_opening_every_game_page_is_locked_on_the_server(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-20 13:59:59', 'Africa/Lusaka'));

        foreach (['/play', '/play/1', '/play/1/gift', '/finale'] as $url) {
            $this->get($url)->assertRedirect('/');
        }
        $this->postJson('/play/1/check', ['cells' => ['0,0' => 'A']])->assertForbidden();
        $this->assertSame([], GameProgress::first()->completed_levels);
    }

    public function test_at_14_00_the_button_and_game_unlock(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-20 14:00:00', 'Africa/Lusaka'));

        $this->get('/')->assertOk()->assertSee('Start My Adventure')->assertSee('href="'.url('/play').'"', false);
        $this->get('/play')->assertOk();
        $this->get('/play/1')->assertOk();
    }

    public function test_zambian_time_is_used_not_the_servers_clock(): void
    {
        // 12:00 UTC is exactly 14:00 in Zambia.
        Carbon::setTestNow(Carbon::parse('2026-11-20 11:59:59', 'UTC'));
        $this->get('/play')->assertRedirect('/');

        Carbon::setTestNow(Carbon::parse('2026-11-20 12:00:00', 'UTC'));
        $this->get('/play')->assertOk();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }
}

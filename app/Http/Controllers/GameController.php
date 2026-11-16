<?php

namespace App\Http\Controllers;

use App\Models\BirthdayGame;
use App\Models\GameLevel;
use App\Models\GameProgress;
use App\Services\CrosswordPuzzle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class GameController extends Controller
{
    private function game(): BirthdayGame
    {
        return BirthdayGame::with('levels.gift')->firstOrFail();
    }

    private function progress(BirthdayGame $game): GameProgress
    {
        return $game->progress ?? GameProgress::create([
            'game_id' => $game->id,
            'completed_levels' => [],
            'revealed_gifts' => [],
        ]);
    }

    private function level(BirthdayGame $game, int $number): GameLevel
    {
        return $game->levels->firstWhere('level_number', $number) ?? abort(404);
    }

    /** A level is playable once the previous one is complete. */
    private function isUnlocked(GameProgress $progress, int $number): bool
    {
        return $number === 1 || in_array($number - 1, $progress->completed_levels, true);
    }

    public function dashboard(): View
    {
        $game = $this->game();
        $progress = $this->progress($game);
        $done = $progress->completed_levels;

        $levels = $game->levels->map(function (GameLevel $level) use ($done, $progress) {
            $completed = in_array($level->level_number, $done, true);
            $state = $completed ? 'completed'
                : ($this->isUnlocked($progress, $level->level_number) ? 'available' : 'locked');

            return [
                'number' => $level->level_number,
                'title' => $level->title,
                'state' => $state,
                // Gift details are only exposed once the level is complete.
                'gift' => $completed ? $level->gift->reveal_heading : null,
            ];
        });

        return view('game.dashboard', [
            'levels' => $levels,
            'completed' => count($done),
            'total' => $levels->count(),
            'allDone' => count($done) >= $levels->count() && $levels->count() > 0,
        ]);
    }

    public function show(int $number): View|RedirectResponse
    {
        $game = $this->game();
        $progress = $this->progress($game);
        $level = $this->level($game, $number);

        if (! $this->isUnlocked($progress, $number)) {
            return redirect('/play');
        }
        if (in_array($number, $progress->completed_levels, true)) {
            return redirect("/play/{$number}/gift");
        }

        return view('game.crossword', [
            'level' => $level,
            'puzzle' => CrosswordPuzzle::payload($level),
        ]);
    }

    public function check(Request $request, int $number): JsonResponse
    {
        $game = $this->game();
        $progress = $this->progress($game);
        $level = $this->level($game, $number);

        // Locked levels can never be solved by posting answers directly.
        abort_unless($this->isUnlocked($progress, $number), 403);

        $data = $request->validate([
            'cells' => ['required', 'array', 'max:400'],
            'cells.*' => ['nullable', 'string', 'max:4'],
        ]);

        $evaluation = CrosswordPuzzle::evaluate($level, $data['cells']);
        $solved = CrosswordPuzzle::isSolved($evaluation);

        if ($solved) {
            $completed = array_values(array_unique([...$progress->completed_levels, $number]));
            $gifts = array_values(array_unique([...$progress->revealed_gifts, $level->gift_id]));
            $progress->update(['completed_levels' => $completed, 'revealed_gifts' => $gifts]);
        }

        return response()->json([
            'words' => $evaluation,
            'solved' => $solved,
            'redirect' => $solved ? url("/play/{$number}/gift") : null,
        ]);
    }

    public function gift(int $number): View|RedirectResponse
    {
        $game = $this->game();
        $progress = $this->progress($game);
        $level = $this->level($game, $number);

        if (! in_array($number, $progress->completed_levels, true)) {
            return redirect('/play');
        }

        $isLast = $number >= $game->levels->max('level_number');

        return view('game.gift-reveal', [
            'level' => $level,
            'gift' => $level->gift,
            'nextUrl' => $isLast ? url('/finale') : url('/play/'.($number + 1)),
            'isLast' => $isLast,
        ]);
    }

    public function finale(): View|RedirectResponse
    {
        $game = $this->game();
        $progress = $this->progress($game);

        if ($game->levels->isEmpty() || count($progress->completed_levels) < $game->levels->count()) {
            return redirect('/play');
        }

        return view('game.finale', ['message' => $game->birthday_message]);
    }
}

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

    public function welcome(): View
    {
        $game = BirthdayGame::first();
        $started = $game && count($this->progress($game)->completed_levels) > 0;

        return view('welcome', ['started' => $started]);
    }

    public function dashboard(): View
    {
        $game = $this->game();
        $progress = $this->progress($game);
        $done = $progress->completed_levels;

        // The current level is the first one not yet completed.
        $current = $game->levels->first(fn (GameLevel $l) => ! in_array($l->level_number, $done, true))?->level_number;

        $levels = $game->levels->map(function (GameLevel $level) use ($done, $current) {
            $completed = in_array($level->level_number, $done, true);

            return [
                'number' => $level->level_number,
                'title' => $level->title,
                'state' => $completed ? 'completed' : ($level->level_number === $current ? 'current' : 'locked'),
                // Gift details are only exposed once the level is complete.
                'gift' => $completed ? $level->gift->reveal_heading : null,
            ];
        });

        $allDone = $levels->isNotEmpty() && $current === null;

        return view('game.dashboard', [
            'levels' => $levels,
            'completed' => count($done),
            'total' => $levels->count(),
            'allDone' => $allDone,
            'current' => $current,
            'continueUrl' => $allDone ? url('/finale') : ($current ? url("/play/{$current}") : url('/play')),
            'recipient' => config('game.recipient'),
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

        $puzzle = CrosswordPuzzle::payload($level);
        // Only words she has already solved are sent back with their letters.
        $puzzle['solved'] = CrosswordPuzzle::answersFor($level, $progress->solvedWordsFor($number));

        return view('game.crossword', [
            'level' => $level,
            'puzzle' => $puzzle,
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

        // Words already saved as solved stay solved, even if the browser lost its letters.
        $saved = $progress->solvedWordsFor($number);
        foreach ($saved as $id) {
            if (isset($evaluation[$id])) {
                $evaluation[$id] = CrosswordPuzzle::CORRECT;
            }
        }
        $solved = CrosswordPuzzle::isSolved($evaluation);

        // Save progress after every check: newly correct words, and the level once complete.
        $correctNow = array_keys(array_filter($evaluation, fn ($s) => $s === CrosswordPuzzle::CORRECT));
        $solvedWords = $progress->solved_words ?? [];
        $solvedWords[$number] = array_values(array_unique([...$saved, ...$correctNow]));

        $attributes = ['solved_words' => $solvedWords];
        if ($solved) {
            $attributes['completed_levels'] = array_values(array_unique([...$progress->completed_levels, $number]));
            $attributes['revealed_gifts'] = array_values(array_unique([...$progress->revealed_gifts, $level->gift_id]));
        }
        $progress->update($attributes);

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

        return view('game.finale', [
            'message' => $game->birthday_message,
            'gifts' => $game->levels->map->gift,
        ]);
    }
}

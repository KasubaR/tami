<?php

namespace Database\Seeders;

use App\Models\CrosswordWord;
use App\Models\GameLevel;
use Illuminate\Database\Seeder;

/**
 * Crossword content, keyed by level number. Each word:
 * [answer, clue, direction, start_row, start_col] (rows/cols are 0-based).
 *
 * PLACEHOLDER: level 1 holds a small sample puzzle so the engine can be
 * exercised. Replace it, and add levels 2-11, with the real puzzles.
 */
class CrosswordWordSeeder extends Seeder
{
    private const PUZZLES = [
        1 => [
            ['PARTY', 'A birthday gathering', 'across', 0, 0],
            ['PINK', 'Colour of a flamingo', 'down', 0, 0],
            ['RING', 'Worn on a finger', 'down', 0, 2],
            ['YEAR', '365 days', 'down', 0, 4],
            ['NUN', 'Lives in a convent', 'across', 2, 0],
        ],
    ];

    public function run(): void
    {
        foreach (self::PUZZLES as $number => $words) {
            $level = GameLevel::where('level_number', $number)->first();
            if (! $level) {
                continue;
            }

            foreach ($words as [$answer, $clue, $direction, $row, $col]) {
                CrosswordWord::updateOrCreate(
                    ['level_id' => $level->id, 'direction' => $direction, 'start_row' => $row, 'start_col' => $col],
                    ['answer' => $answer, 'clue' => $clue],
                );
            }
        }
    }
}

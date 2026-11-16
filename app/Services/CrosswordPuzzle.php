<?php

namespace App\Services;

use App\Models\CrosswordWord;
use App\Models\GameLevel;
use Illuminate\Support\Collection;

/**
 * Builds the browser-safe description of a level's crossword and checks
 * submitted letters against the answer key. Answers never leave this class.
 */
class CrosswordPuzzle
{
    public const CORRECT = 'correct';
    public const WRONG = 'wrong';
    public const INCOMPLETE = 'incomplete';

    /** Uppercase and strip everything that is not a letter or digit. */
    public static function normalize(?string $value): string
    {
        $value = preg_replace('/[^\p{L}\p{N}]+/u', '', (string) $value);

        return mb_strtoupper($value ?? '');
    }

    /** @return Collection<int, CrosswordWord> */
    private static function words(GameLevel $level): Collection
    {
        return $level->words()->orderBy('start_row')->orderBy('start_col')->orderBy('direction')->get();
    }

    /** @return list<array{0:int,1:int}> cells covered by a word */
    private static function cellsOf(CrosswordWord $word): array
    {
        $length = mb_strlen(self::normalize($word->answer));
        $cells = [];
        for ($i = 0; $i < $length; $i++) {
            $cells[] = $word->direction === 'across'
                ? [$word->start_row, $word->start_col + $i]
                : [$word->start_row + $i, $word->start_col];
        }

        return $cells;
    }

    /**
     * Data sent to the browser: grid size, numbered words, clues and lengths.
     * Deliberately contains no answers and no gift information.
     */
    public static function payload(GameLevel $level): array
    {
        $words = self::words($level);

        // Number start cells in reading order; across and down words that
        // begin on the same cell share a number.
        $numbers = [];
        $next = 1;
        foreach ($words as $word) {
            $key = $word->start_row.','.$word->start_col;
            $numbers[$key] ??= $next++;
        }

        $rows = 0;
        $cols = 0;
        $list = [];
        foreach ($words as $word) {
            $cells = self::cellsOf($word);
            foreach ($cells as [$r, $c]) {
                $rows = max($rows, $r + 1);
                $cols = max($cols, $c + 1);
            }
            $list[] = [
                'id' => $word->id,
                'number' => $numbers[$word->start_row.','.$word->start_col],
                'direction' => $word->direction,
                'row' => $word->start_row,
                'col' => $word->start_col,
                'length' => count($cells),
                'clue' => $word->clue,
            ];
        }

        return ['rows' => $rows, 'cols' => $cols, 'words' => $list];
    }

    /**
     * @param  array<string, mixed>  $cells  map of "row,col" => single letter
     * @return array<int, string>  word id => correct | wrong | incomplete
     */
    public static function evaluate(GameLevel $level, array $cells): array
    {
        $normalized = [];
        foreach ($cells as $key => $letter) {
            if (is_string($key) && is_string($letter)) {
                $normalized[$key] = self::normalize($letter);
            }
        }

        $result = [];
        foreach (self::words($level) as $word) {
            $guess = '';
            $complete = true;
            foreach (self::cellsOf($word) as [$r, $c]) {
                $letter = $normalized["$r,$c"] ?? '';
                if (mb_strlen($letter) !== 1) {
                    $complete = false;
                    break;
                }
                $guess .= $letter;
            }

            $result[$word->id] = ! $complete
                ? self::INCOMPLETE
                : (hash_equals(self::normalize($word->answer), $guess) ? self::CORRECT : self::WRONG);
        }

        return $result;
    }

    public static function isSolved(array $evaluation): bool
    {
        return $evaluation !== [] && collect($evaluation)->every(fn ($s) => $s === self::CORRECT);
    }
}

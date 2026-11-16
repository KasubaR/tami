<?php

namespace Tests\Feature;

use App\Models\GameLevel;
use App\Services\CrosswordPuzzle;
use Database\Seeders\BirthdayGameSeeder;
use Database\Seeders\CrosswordWordSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Validates the real puzzle content shipped in the seeders. */
class PuzzleContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([BirthdayGameSeeder::class, CrosswordWordSeeder::class]);
    }

    /** @return array<int, GameLevel> */
    private function levels(): array
    {
        return GameLevel::with(['gift', 'words'])->orderBy('level_number')->get()->all();
    }

    /** Build "r,c" => letter for a level, failing on any letter clash. */
    private function lettersOf(GameLevel $level): array
    {
        $cells = [];
        foreach ($level->words as $w) {
            $answer = CrosswordPuzzle::normalize($w->answer);
            foreach (mb_str_split($answer) as $i => $ch) {
                $r = $w->start_row + ($w->direction === 'down' ? $i : 0);
                $c = $w->start_col + ($w->direction === 'across' ? $i : 0);
                $this->assertTrue(
                    ! isset($cells["$r,$c"]) || $cells["$r,$c"] === $ch,
                    "Level {$level->level_number}: letter clash at $r,$c ({$w->answer})"
                );
                $cells["$r,$c"] = $ch;
            }
        }

        return $cells;
    }

    public function test_all_eleven_levels_have_a_puzzle(): void
    {
        $levels = $this->levels();
        $this->assertCount(11, $levels);

        foreach ($levels as $level) {
            $count = $level->words->count();
            $this->assertGreaterThanOrEqual(5, $count, "Level {$level->level_number} has too few words");
            $this->assertLessThanOrEqual(8, $count, "Level {$level->level_number} has too many words");
        }
    }

    public function test_each_puzzle_contains_its_gift_as_the_main_answer(): void
    {
        foreach ($this->levels() as $level) {
            $answers = $level->words->map(fn ($w) => CrosswordPuzzle::normalize($w->answer))->all();
            $this->assertContains(
                CrosswordPuzzle::normalize($level->gift->name),
                $answers,
                "Level {$level->level_number} is missing its gift word"
            );
        }
    }

    public function test_grids_are_valid_connected_and_have_no_stray_words(): void
    {
        foreach ($this->levels() as $level) {
            $n = $level->level_number;
            $cells = $this->lettersOf($level);
            $words = $level->words->all();

            // Every run of 2+ letters, in both directions, must be a listed word.
            $runs = [];
            $maxR = max(array_map(fn ($k) => (int) explode(',', $k)[0], array_keys($cells)));
            $maxC = max(array_map(fn ($k) => (int) explode(',', $k)[1], array_keys($cells)));
            $this->assertLessThanOrEqual(12, $maxR + 1, "Level $n too tall");
            $this->assertLessThanOrEqual(12, $maxC + 1, "Level $n too wide");

            for ($r = 0; $r <= $maxR; $r++) {
                $run = '';
                $start = 0;
                for ($c = 0; $c <= $maxC + 1; $c++) {
                    $ch = $cells["$r,$c"] ?? null;
                    if ($ch !== null) {
                        $run === '' && $start = $c;
                        $run .= $ch;
                    } else {
                        strlen($run) > 1 && $runs["across:$r:$start:$run"] = true;
                        $run = '';
                    }
                }
            }
            for ($c = 0; $c <= $maxC; $c++) {
                $run = '';
                $start = 0;
                for ($r = 0; $r <= $maxR + 1; $r++) {
                    $ch = $cells["$r,$c"] ?? null;
                    if ($ch !== null) {
                        $run === '' && $start = $r;
                        $run .= $ch;
                    } else {
                        strlen($run) > 1 && $runs["down:$start:$c:$run"] = true;
                        $run = '';
                    }
                }
            }

            $listed = [];
            foreach ($words as $w) {
                $listed["{$w->direction}:{$w->start_row}:{$w->start_col}:".CrosswordPuzzle::normalize($w->answer)] = true;
            }
            $this->assertSame([], array_keys(array_diff_key($runs, $listed)), "Level $n has unlisted letter runs");
            $this->assertSame([], array_keys(array_diff_key($listed, $runs)), "Level $n has words that don't form a run");

            // Connected: every word shares a cell with the growing group.
            $sets = array_map(function ($w) {
                $out = [];
                foreach (range(0, mb_strlen(CrosswordPuzzle::normalize($w->answer)) - 1) as $i) {
                    $r = $w->start_row + ($w->direction === 'down' ? $i : 0);
                    $c = $w->start_col + ($w->direction === 'across' ? $i : 0);
                    $out["$r,$c"] = true;
                }

                return $out;
            }, $words);
            $group = $sets[0];
            $remaining = array_slice($sets, 1);
            do {
                $grew = false;
                foreach ($remaining as $i => $s) {
                    if (array_intersect_key($s, $group)) {
                        $group += $s;
                        unset($remaining[$i]);
                        $grew = true;
                    }
                }
            } while ($grew);
            $this->assertSame([], $remaining, "Level $n is not fully connected");

            // At least two crossings so it behaves like a real crossword.
            $crossings = 0;
            foreach ($cells as $k => $_) {
                [$r, $c] = array_map('intval', explode(',', $k));
                $across = isset($cells[$r.','.($c - 1)]) || isset($cells[$r.','.($c + 1)]);
                $down = isset($cells[($r - 1).",$c"]) || isset($cells[($r + 1).",$c"]);
                $across && $down && $crossings++;
            }
            $this->assertGreaterThanOrEqual(4, $crossings, "Level $n has too few intersections");
        }
    }

    public function test_answers_are_unique_clean_and_clues_do_not_leak(): void
    {
        foreach ($this->levels() as $level) {
            $n = $level->level_number;
            $answers = [];
            foreach ($level->words as $w) {
                $this->assertMatchesRegularExpression('/^[A-Z]+$/', $w->answer, "Level $n answer {$w->answer} must be A-Z only");
                $this->assertGreaterThanOrEqual(3, strlen($w->answer));
                $this->assertNotSame('', trim($w->clue));
                $answers[] = $w->answer;
            }
            $this->assertSame($answers, array_values(array_unique($answers)), "Level $n repeats an answer");

            foreach ($level->words as $w) {
                foreach ($answers as $other) {
                    if ($other === $w->answer) {
                        continue;
                    }
                    $this->assertStringNotContainsString(
                        strtolower($other),
                        strtolower($w->clue),
                        "Level $n: clue for {$w->answer} gives away {$other}"
                    );
                }
                // A clue never contains its own answer either.
                $this->assertStringNotContainsString(strtolower($w->answer), strtolower($w->clue));
            }
        }
    }

    public function test_every_puzzle_can_be_solved_through_the_server(): void
    {
        foreach ($this->levels() as $level) {
            $cells = $this->lettersOf($level);

            $evaluation = CrosswordPuzzle::evaluate($level, $cells);
            $this->assertTrue(CrosswordPuzzle::isSolved($evaluation), "Level {$level->level_number} cannot be solved");

            // Changing any one letter must make the puzzle unsolved.
            $firstKey = array_key_first($cells);
            $broken = $cells;
            $broken[$firstKey] = $cells[$firstKey] === 'Z' ? 'Q' : 'Z';
            $this->assertFalse(CrosswordPuzzle::isSolved(CrosswordPuzzle::evaluate($level, $broken)));
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\BirthdayGame;
use App\Models\GameLevel;
use App\Models\GameProgress;
use App\Models\Gift;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BirthdayGameSeeder extends Seeder
{
    /** level => [gift name, reveal heading] */
    private const GIFTS = [
        1 => ['Shower pack', 'The First Surprise'],
        2 => ['Straightener', 'A Little Beauty'],
        3 => ['Sneakers', 'Step in Style'],
        4 => ['Flats', 'The Perfect Pair'],
        5 => ['Diary', 'Happiness'],
        6 => ['Avon', 'Beauty Secrets'],
        7 => ['Power bank', 'Stay Connected'],
        8 => ['Jeans', 'Denim Dreams'],
        9 => ['Hair dryer', 'Beauty Goals'],
        10 => ['Cake', 'Sweet Celebration'],
        11 => ['Purse', 'The Final Surprise'],
    ];

    public function run(): void
    {
        // Idempotent: re-running keeps the existing access token and progress.
        $game = BirthdayGame::firstOrCreate(
            ['name' => "Tamara's Birthday Mystery"],
            [
                // Placeholder: replace with the real final message.
                'birthday_message' => 'Happy Birthday, Tamara! You solved all eleven puzzles. 🥂',
                'access_token' => Str::random(40),
            ],
        );

        foreach (self::GIFTS as $number => [$giftName, $heading]) {
            $gift = Gift::updateOrCreate(
                ['name' => $giftName],
                [
                    'reveal_heading' => $heading,
                    // Placeholder: replace with a personal message per gift.
                    'reveal_message' => "Puzzle solved! Your gift is: {$giftName}.",
                ],
            );

            GameLevel::updateOrCreate(
                ['game_id' => $game->id, 'level_number' => $number],
                ['title' => "Puzzle {$number}", 'gift_id' => $gift->id],
            );
        }

        GameProgress::firstOrCreate(
            ['game_id' => $game->id],
            ['completed_levels' => [], 'revealed_gifts' => []],
        );
    }
}

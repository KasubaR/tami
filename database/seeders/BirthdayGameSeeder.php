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
    /**
     * level => [gift name, icon (file name in resources/svg), reveal heading, personal message]
     * Edit the wording here and re-run `php artisan db:seed`.
     */
    private const GIFTS = [
        1 => ['Cake', 'cake', 'The First Surprise', 'No birthday is complete without something sweet. Make a wish!'],
        2 => ['Shower pack', 'shower-head', 'Fresh Start', 'Because every great day starts with a little pampering. Soak it all up, Tamara.'],
        3 => ['Diary', 'notebook-pen', 'Happiness', 'Your thoughts, your dreams, your little secrets. May every page be filled with happiness.'],
        4 => ['Purse', 'shopping-bag', 'Carry It in Style', 'A little something to keep everything you love close by.'],
        5 => ['Straightener', 'wand-sparkles', 'A Little Beauty', 'Smooth, sleek and shining, just like you. Let your hair do the talking.'],
        6 => ['Flats', 'footprints', 'The Perfect Pair', 'Comfort and elegance in one. Made for dancing all night long.'],
        7 => ['Hair dryer', 'wind', 'Beauty Goals', 'Big hair days and easy mornings. Time to make every day a good hair day.'],
        8 => ['Power bank', 'battery-charging', 'Stay Connected', 'So your phone never runs out before our conversations do. Stay connected, always.'],
        9 => ['Avon', 'flower-2', 'Beauty Secrets', 'A little glow for a lot of beauty. You are already gorgeous, this just adds sparkle.'],
        10 => ['Jeans', 'shirt', 'Denim Dreams', 'The perfect fit for every adventure. Casual, cool and so completely you.'],
        11 => ['Sneakers', 'footprints', 'The Final Surprise', 'Saved the best for last. Because every step deserves a little extra style.'],
    ];

    public function run(): void
    {
        // Re-running keeps the access token and progress; wording is refreshed.
        $game = BirthdayGame::firstOrCreate(
            ['name' => "Tamara's Birthday Mystery"],
            ['birthday_message' => '', 'access_token' => Str::random(40)],
        );
        $game->update([
            // Draft: replace with your own final message.
            'birthday_message' => "Happy Birthday! You solved all eleven puzzles and unwrapped every single surprise. "
                ."Today is all about you, and I hope it is as wonderful as you are.",
        ]);

        foreach (self::GIFTS as $number => [$giftName, $icon, $heading, $message]) {
            $gift = Gift::updateOrCreate(
                ['name' => $giftName],
                ['icon' => $icon, 'reveal_heading' => $heading, 'reveal_message' => $message],
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

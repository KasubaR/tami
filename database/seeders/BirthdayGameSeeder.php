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
     * level => [gift name, emoji, reveal heading, personal message]
     * Edit the wording here and re-run `php artisan db:seed`.
     */
    private const GIFTS = [
        1 => ['Shower pack', '🧴', 'The First Surprise', 'Because every great day starts with a little pampering. Soak it all up, Tamara.'],
        2 => ['Straightener', '✨', 'A Little Beauty', 'Smooth, sleek and shining, just like you. Let your hair do the talking.'],
        3 => ['Sneakers', '👟', 'Step in Style', 'Because every step deserves a little extra style.'],
        4 => ['Flats', '🥿', 'The Perfect Pair', 'Comfort and elegance in one. Made for dancing all night long.'],
        5 => ['Diary', '📔', 'Happiness', 'Your thoughts, your dreams, your little secrets. May every page be filled with happiness.'],
        6 => ['Avon', '💄', 'Beauty Secrets', 'A little glow for a lot of beauty. You are already gorgeous, this just adds sparkle.'],
        7 => ['Power bank', '🔋', 'Stay Connected', 'So your phone never runs out before our conversations do. Stay connected, always.'],
        8 => ['Jeans', '👖', 'Denim Dreams', 'The perfect fit for every adventure. Casual, cool and so completely you.'],
        9 => ['Hair dryer', '💇‍♀️', 'Beauty Goals', 'Big hair days and easy mornings. Time to make every day a good hair day.'],
        10 => ['Cake', '🎂', 'Sweet Celebration', 'No birthday is complete without something sweet. Make a wish!'],
        11 => ['Purse', '👛', 'The Final Surprise', 'Saved the best for last. A little something to keep everything you love close by.'],
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
                ."Today is all about you, and I hope it is as wonderful as you are. 🥂",
        ]);

        foreach (self::GIFTS as $number => [$giftName, $emoji, $heading, $message]) {
            $gift = Gift::updateOrCreate(
                ['name' => $giftName],
                ['emoji' => $emoji, 'reveal_heading' => $heading, 'reveal_message' => $message],
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

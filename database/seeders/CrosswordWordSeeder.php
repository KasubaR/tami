<?php

namespace Database\Seeders;

use App\Models\CrosswordWord;
use App\Models\GameLevel;
use Illuminate\Database\Seeder;

/**
 * The eleven crossword puzzles, keyed by level number.
 *
 * Each word: [answer, clue, direction, start_row, start_col] (0-based).
 * Every puzzle has one main answer (the gift) plus supporting words that
 * genuinely intersect it. Layouts are verified by tests/Feature/PuzzleContentTest.php.
 */
class CrosswordWordSeeder extends Seeder
{
    private const PUZZLES = [
        1 => [
            ['LATHER', 'Rich, frothy suds from shampoo', 'down', 0, 7],
            ['SHOWERPACK', 'A bundle of body-wash essentials for getting clean', 'across', 1, 0],
            ['SCRUB', 'Rub hard to get clean', 'down', 1, 0],
            ['RINSE', 'Wash off the suds', 'down', 1, 5],
            ['TUB', 'Large container for a long soak', 'down', 3, 2],
            ['STEAM', 'Hot water vapour that fogs a bathroom mirror', 'across', 4, 5],
            ['BUBBLE', 'Round, floating ball of soapy air', 'across', 5, 0],
        ],
        2 => [
            ['TRESS', 'A long lock of hair', 'down', 0, 2],
            ['SILKY', 'Soft and smooth like a luxury fabric', 'down', 0, 4],
            ['SHINE', 'Healthy glossy look', 'down', 0, 6],
            ['HEAT', 'Warmth that styling tools produce', 'down', 0, 8],
            ['FRIZZ', 'Unruly, puffy hair in humid weather', 'down', 0, 11],
            ['STRAIGHTENER', 'An appliance that helps create smooth, sleek hair', 'across', 1, 0],
            ['STYLE', 'A fashionable look', 'across', 4, 2],
        ],
        3 => [
            ['SOLE', 'Bottom part of a shoe', 'across', 0, 3],
            ['STRIDE', 'A long, confident step', 'down', 0, 3],
            ['LACES', 'Threaded through eyelets and tied in a bow', 'down', 1, 1],
            ['PAIR', 'Two of a kind', 'across', 2, 0],
            ['RACE', 'Competition of speed', 'down', 2, 6],
            ['WALK', 'Go on foot at an easy pace', 'across', 3, 5],
            ['SNEAKERS', 'Comfortable footwear commonly used for exercise', 'across', 5, 1],
        ],
        4 => [
            ['COMFY', 'Cosy and easy to wear', 'down', 0, 0],
            ['STRAP', 'Narrow band that holds a sandal on', 'down', 0, 2],
            ['INSOLE', 'Cushion inside a shoe', 'down', 1, 4],
            ['FLATS', 'Footwear with little or no heel', 'across', 3, 0],
            ['STEP', 'One movement of your foot', 'down', 5, 1],
            ['STYLE', 'A fashionable look', 'across', 6, 0],
            ['PAIR', 'Two of a kind', 'across', 8, 1],
        ],
        5 => [
            ['PAGES', 'Sheets bound together in a book', 'across', 0, 1],
            ['PEN', 'Handheld writing tool', 'down', 0, 1],
            ['STORY', 'A tale with a beginning and an end', 'down', 0, 5],
            ['DEAR', 'Common first word of a letter', 'down', 1, 7],
            ['INK', 'Coloured liquid used for writing', 'across', 2, 0],
            ['NOTE', 'A short message jotted down', 'across', 2, 4],
            ['DIARY', 'A place to record personal thoughts', 'across', 4, 1],
        ],
        6 => [
            ['GLOSS', 'Shiny lip product', 'down', 0, 0],
            ['TONER', 'Skincare step after cleansing', 'down', 0, 2],
            ['LOTION', 'Skin moisturiser you pour or pump', 'down', 1, 4],
            ['SCENT', 'A pleasant smell', 'across', 3, 0],
            ['NAIL', 'It can be polished', 'down', 5, 1],
            ['AVON', 'Cosmetics brand sold through personal representatives', 'across', 6, 1],
            ['BLUSH', 'Pink powder for the cheeks', 'across', 8, 0],
        ],
        7 => [
            ['SOCKET', 'Wall outlet', 'down', 0, 8],
            ['USB', 'Common type of computer port', 'across', 1, 3],
            ['SCREEN', 'Display on a phone', 'down', 1, 4],
            ['WATT', 'Unit of power', 'down', 2, 6],
            ['POWERBANK', 'A portable device that recharges your phone', 'across', 3, 0],
            ['WIRE', 'Thin metal strand that carries electricity', 'down', 3, 2],
            ['CURRENT', 'Flow of electricity', 'across', 5, 0],
        ],
        8 => [
            ['BELT', 'Worn around the waist to hold trousers up', 'across', 0, 0],
            ['BLUE', 'The most classic colour for this fabric', 'down', 0, 0],
            ['THREAD', 'Fine strand used for sewing', 'down', 0, 3],
            ['LEGS', 'You have two of these', 'down', 0, 6],
            ['HEM', 'Folded and sewn bottom edge', 'across', 1, 5],
            ['JEANS', 'Trousers commonly made from denim', 'across', 3, 2],
            ['INDIGO', 'Dark dye from a tropical plant, used to colour fabric', 'across', 5, 1],
        ],
        9 => [
            ['BLOW', 'Send out a strong stream of air', 'across', 0, 0],
            ['WARM', 'Pleasantly hot', 'down', 0, 3],
            ['CURLS', 'Spiral locks', 'down', 0, 5],
            ['BRUSH', 'Round styling tool with bristles', 'down', 1, 8],
            ['HAIRDRYER', 'An appliance used to dry wet hair', 'across', 2, 0],
            ['HEAT', 'Hot energy', 'down', 2, 0],
            ['TOWEL', 'Absorbs water after a bath', 'across', 5, 0],
        ],
        10 => [
            ['VANILLA', 'Popular flavour that comes from an orchid pod', 'down', 0, 2],
            ['CAKE', 'A sweet treat commonly served at birthdays', 'across', 1, 1],
            ['EGGS', 'Cracked into a batter', 'down', 1, 4],
            ['SPONGE', 'Light, airy type of baked treat', 'down', 1, 6],
            ['WISH', 'A silent hope made before blowing out the flame', 'down', 2, 0],
            ['ICING', 'Sugary topping spread on a sweet treat', 'across', 3, 0],
            ['CANDLE', 'Waxy stick with a wick, blown out on top of a birthday treat', 'across', 6, 1],
        ],
        11 => [
            ['CASH', 'Money you can hold in your hand', 'across', 0, 0],
            ['CARDS', 'Plastic rectangles for payments', 'down', 0, 0],
            ['STRAP', 'Band you hang over your shoulder', 'down', 0, 2],
            ['COINS', 'Small round pieces of metal money', 'down', 0, 5],
            ['KEYS', 'Open doors and start cars', 'down', 0, 7],
            ['TOTE', 'Large open carry bag', 'across', 1, 4],
            ['PURSE', 'An accessory used to carry personal essentials', 'across', 4, 2],
        ],
    ];

    public function run(): void
    {
        foreach (self::PUZZLES as $number => $words) {
            $level = GameLevel::where('level_number', $number)->first();
            if (! $level) {
                continue;
            }

            // Replace the level's words so re-seeding always matches this file.
            $level->words()->delete();

            foreach ($words as [$answer, $clue, $direction, $row, $col]) {
                CrosswordWord::create([
                    'level_id' => $level->id,
                    'answer' => $answer,
                    'clue' => $clue,
                    'direction' => $direction,
                    'start_row' => $row,
                    'start_col' => $col,
                ]);
            }
        }
    }
}

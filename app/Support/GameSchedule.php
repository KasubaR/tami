<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/** When the adventure opens. All times are Zambian time (Africa/Lusaka, UTC+2). */
class GameSchedule
{
    public const TIMEZONE = 'Africa/Lusaka';

    public static function opensAt(): Carbon
    {
        return Carbon::parse(config('game.opens_at'), self::TIMEZONE);
    }

    public static function isOpen(): bool
    {
        // Local development shortcut (see config/game.php); off unless explicitly enabled.
        if (config('game.open_access') && app()->isLocal()) {
            return true;
        }

        return now()->greaterThanOrEqualTo(self::opensAt());
    }
}

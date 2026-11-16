<?php

namespace App\Console\Commands;

use App\Models\BirthdayGame;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GameLink extends Command
{
    protected $signature = 'game:link {--rotate : Create a new token. The old link stops working immediately.}';

    protected $description = 'Show the private link for the birthday game';

    public function handle(): int
    {
        $game = BirthdayGame::first();

        if (! $game) {
            $this->error('No game found. Run: php artisan db:seed');

            return self::FAILURE;
        }

        if ($this->option('rotate')) {
            $game->update(['access_token' => Str::random(40)]);
            $this->warn('Token rotated. The previous link and all open sessions are now invalid.');
        }

        $this->info('Private link (share it only with the birthday girl):');
        $this->line(rtrim(config('app.url'), '/').'/g/'.$game->access_token);

        return self::SUCCESS;
    }
}

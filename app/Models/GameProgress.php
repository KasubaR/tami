<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameProgress extends Model
{
    protected $table = 'game_progress';

    protected $fillable = ['game_id', 'completed_levels', 'revealed_gifts'];

    protected function casts(): array
    {
        return [
            'completed_levels' => 'array',
            'revealed_gifts' => 'array',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(BirthdayGame::class, 'game_id');
    }
}

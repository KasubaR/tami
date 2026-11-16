<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameProgress extends Model
{
    protected $table = 'game_progress';

    protected $fillable = ['game_id', 'completed_levels', 'revealed_gifts', 'solved_words'];

    protected function casts(): array
    {
        return [
            'completed_levels' => 'array',
            'revealed_gifts' => 'array',
            'solved_words' => 'array',
        ];
    }

    /** @return list<int> word ids already solved on a level */
    public function solvedWordsFor(int $levelNumber): array
    {
        return array_map('intval', ($this->solved_words ?? [])[$levelNumber] ?? []);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(BirthdayGame::class, 'game_id');
    }
}

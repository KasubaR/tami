<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameLevel extends Model
{
    protected $fillable = ['game_id', 'level_number', 'title', 'gift_id'];

    public function game(): BelongsTo
    {
        return $this->belongsTo(BirthdayGame::class, 'game_id');
    }

    public function gift(): BelongsTo
    {
        return $this->belongsTo(Gift::class);
    }

    public function words(): HasMany
    {
        return $this->hasMany(CrosswordWord::class, 'level_id');
    }
}

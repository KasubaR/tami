<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrosswordWord extends Model
{
    protected $fillable = ['level_id', 'answer', 'clue', 'direction', 'start_row', 'start_col'];

    // Answers stay server-side; they are checked there, never sent to the browser.
    protected $hidden = ['answer'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(GameLevel::class, 'level_id');
    }
}

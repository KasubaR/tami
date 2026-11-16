<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BirthdayGame extends Model
{
    protected $fillable = ['name', 'birthday_message', 'access_token'];

    protected $hidden = ['access_token'];

    public function levels(): HasMany
    {
        return $this->hasMany(GameLevel::class, 'game_id')->orderBy('level_number');
    }

    public function progress(): HasOne
    {
        return $this->hasOne(GameProgress::class, 'game_id');
    }
}

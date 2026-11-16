<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gift extends Model
{
    protected $fillable = ['name', 'reveal_heading', 'reveal_message', 'image'];
}

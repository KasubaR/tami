<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('birthday_games')->cascadeOnDelete();
            $table->unsignedTinyInteger('level_number');
            $table->string('title');
            $table->foreignId('gift_id')->constrained('gifts')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['game_id', 'level_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_levels');
    }
};

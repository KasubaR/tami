<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crossword_words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained('game_levels')->cascadeOnDelete();
            $table->string('answer');
            $table->string('clue');
            $table->enum('direction', ['across', 'down']);
            $table->unsignedTinyInteger('start_row');
            $table->unsignedTinyInteger('start_col');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crossword_words');
    }
};

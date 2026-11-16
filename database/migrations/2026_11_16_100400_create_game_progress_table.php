<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->unique()->constrained('birthday_games')->cascadeOnDelete();
            $table->json('completed_levels'); // e.g. [1, 2, 3]
            $table->json('revealed_gifts');   // e.g. [1, 2, 3] (gift ids)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_progress');
    }
};

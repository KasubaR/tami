<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_progress', function (Blueprint $table) {
            // { "<level number>": [<crossword word ids already solved>] }
            $table->json('solved_words')->nullable()->after('revealed_gifts');
        });
    }

    public function down(): void
    {
        Schema::table('game_progress', function (Blueprint $table) {
            $table->dropColumn('solved_words');
        });
    }
};

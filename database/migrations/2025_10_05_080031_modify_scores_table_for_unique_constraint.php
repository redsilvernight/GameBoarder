<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scores', function (Blueprint $table) {
            $table->dropPrimary(['player_id', 'leaderboard_id']);
            $table->id()->first();
            $table->unique(['player_id', 'leaderboard_id'], 'unique_player_leaderboard');
        });
    }

    public function down(): void
    {
        Schema::table('scores', function (Blueprint $table) {
            $table->dropUnique('unique_player_leaderboard');
            $table->dropColumn('id');
            $table->primary(['player_id', 'leaderboard_id']);
        });
    }
};

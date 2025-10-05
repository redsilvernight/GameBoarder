<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scores', function (Blueprint $table) {
            $table->id(); // PK auto-increment
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('leaderboard_id')->constrained()->onDelete('cascade');
            $table->integer('score');
            $table->timestamps();
            $table->unique(['player_id', 'leaderboard_id'], 'unique_player_leaderboard');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};

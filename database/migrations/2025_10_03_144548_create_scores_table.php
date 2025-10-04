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
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('leaderboard_id')->constrained('leaderboards')->onDelete('cascade');
            $table->integer('score');
            $table->timestamps();

            $table->primary(['player_id', 'leaderboard_id']);
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

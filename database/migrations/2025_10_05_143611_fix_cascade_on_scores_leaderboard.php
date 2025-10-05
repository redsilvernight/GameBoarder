<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scores', function (Blueprint $table) {
            // Supprimer la contrainte existante si elle existe
            $table->dropForeign(['leaderboard_id']);

            // Recréer avec cascade
            $table->foreign('leaderboard_id')
                ->references('id')
                ->on('leaderboards')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('scores', function (Blueprint $table) {
            $table->dropForeign(['leaderboard_id']);
            $table->foreignId('leaderboard_id')->constrained('leaderboards');
        });
    }
};

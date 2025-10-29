<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_saves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->unsignedTinyInteger('slot')->default(1);
            $table->string('file_path');
            $table->string('checksum', 64);
            $table->unsignedBigInteger('file_size');
            $table->timestamps();

            $table->unique(['player_id', 'slot']);
            $table->index('game_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_saves');
    }
};

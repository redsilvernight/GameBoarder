
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->enum('save_mode', ['single', 'multiple'])->default('single')->after('id');
            $table->unsignedTinyInteger('max_save_slots')->default(3)->after('save_mode');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['save_mode', 'max_save_slots']);
        });
    }
};

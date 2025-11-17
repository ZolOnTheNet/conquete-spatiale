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
        Schema::table('personnages', function (Blueprint $table) {
            $table->integer('points_action')->default(10)->after('jetons_fear');
            $table->integer('max_points_action')->default(10)->after('points_action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            $table->dropColumn(['points_action', 'max_points_action']);
        });
    }
};

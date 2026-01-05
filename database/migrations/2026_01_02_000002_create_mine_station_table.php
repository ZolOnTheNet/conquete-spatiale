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
        Schema::create('mine_station', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mine_id')->constrained('mines')->onDelete('cascade');
            $table->foreignId('station_id')->constrained('stations')->onDelete('cascade');
            $table->decimal('distance_km', 12, 2)->nullable(); // Distance entre la mine et la station
            $table->timestamps();

            // Index unique pour éviter les doublons
            $table->unique(['mine_id', 'station_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mine_station');
    }
};

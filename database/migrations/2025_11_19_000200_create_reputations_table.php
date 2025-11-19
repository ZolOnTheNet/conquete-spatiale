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
        Schema::create('reputations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnage_id')->constrained('personnages')->onDelete('cascade');
            $table->foreignId('faction_id')->constrained('factions')->onDelete('cascade');

            // Reputation: -1000 (hostile) a +1000 (allie)
            $table->integer('valeur')->default(0);

            // Rang derive de la reputation
            $table->enum('rang', [
                'hostile',      // -1000 a -500
                'inamical',     // -499 a -100
                'neutre',       // -99 a 99
                'amical',       // 100 a 499
                'apprecie',     // 500 a 799
                'honore',       // 800 a 999
                'venere',       // 1000
            ])->default('neutre');

            // Statistiques
            $table->integer('missions_completees')->default(0);
            $table->integer('missions_echouees')->default(0);

            $table->timestamps();

            // Unique par personnage/faction
            $table->unique(['personnage_id', 'faction_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reputations');
    }
};

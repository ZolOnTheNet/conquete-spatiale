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
        Schema::create('factions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('nom', 100);
            $table->text('description')->nullable();

            // Type de faction
            $table->enum('type', [
                'gouvernement',
                'corporation',
                'guilde',
                'pirate',
                'scientifique',
                'militaire',
            ])->default('corporation');

            // Alignement
            $table->enum('alignement', ['legal', 'neutre', 'criminel'])->default('neutre');

            // Relations par defaut avec autres factions
            $table->json('relations')->nullable(); // {'FACTION_CODE': reputation_modifier}

            // Couleur pour UI
            $table->string('couleur', 7)->default('#888888');

            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factions');
    }
};

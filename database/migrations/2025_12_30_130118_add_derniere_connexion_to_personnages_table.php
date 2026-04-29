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
            // Date de dernière connexion IN-GAME (date fictive dans l'univers 3000+)
            // Utilisée pour calculer les positions orbitales des planètes
            // Avance de 0.5 jour par PA dépensé
            $table->timestamp('derniere_connexion')
                ->nullable()
                ->after('derniere_recuperation_pa')
                ->comment('Date in-game actuelle du personnage (3000+)');
        });

        // Initialiser tous les personnages existants à la date de référence
        DB::table('personnages')->update([
            'derniere_connexion' => '3000-01-01 00:00:00'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            $table->dropColumn('derniere_connexion');
        });
    }
};

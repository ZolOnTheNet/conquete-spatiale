<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Changer type_etoile de ENUM à VARCHAR pour supporter les classifications détaillées
        // comme 'G2V', 'M5.5Ve', etc.
        Schema::table('systemes_stellaires', function (Blueprint $table) {
            // MySQL ne permet pas de modifier directement un ENUM en VARCHAR
            // On doit d'abord supprimer puis recréer la colonne
            $table->dropColumn('type_etoile');
        });

        Schema::table('systemes_stellaires', function (Blueprint $table) {
            $table->string('type_etoile', 10)->default('G')->after('nom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('systemes_stellaires', function (Blueprint $table) {
            $table->dropColumn('type_etoile');
        });

        Schema::table('systemes_stellaires', function (Blueprint $table) {
            $table->enum('type_etoile', ['O', 'B', 'A', 'F', 'G', 'K', 'M'])->default('G')->after('nom');
        });
    }
};

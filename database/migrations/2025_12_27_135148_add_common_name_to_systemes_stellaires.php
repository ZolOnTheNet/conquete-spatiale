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
        Schema::table('systemes_stellaires', function (Blueprint $table) {
            // Nom commun/célèbre de l'étoile (ex: "Proxima Centauri", "Sirius", "Alpha Centauri A")
            if (!Schema::hasColumn('systemes_stellaires', 'nom_commun')) {
                $table->string('nom_commun')->nullable()->after('nom')
                    ->comment('Nom commun/célèbre de l\'étoile');
            }

            // Noms alternatifs (JSON) - stocke tous les catalogues (Hipparcos, HD, HR, Bayer, etc.)
            if (!Schema::hasColumn('systemes_stellaires', 'noms_alternatifs')) {
                $table->json('noms_alternatifs')->nullable()->after('nom_commun')
                    ->comment('Noms alternatifs : Hipparcos, HD, HR, Bayer, Flamsteed, etc. (JSON)');
            }
        });

        // Ajouter index pour recherche rapide
        Schema::table('systemes_stellaires', function (Blueprint $table) {
            if (!$this->indexExists('systemes_stellaires', 'systemes_stellaires_nom_commun_index')) {
                $table->index('nom_commun');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('systemes_stellaires', function (Blueprint $table) {
            if (Schema::hasColumn('systemes_stellaires', 'nom_commun')) {
                $table->dropColumn('nom_commun');
            }
            if (Schema::hasColumn('systemes_stellaires', 'noms_alternatifs')) {
                $table->dropColumn('noms_alternatifs');
            }
        });
    }

    /**
     * Vérifier si un index existe
     */
    protected function indexExists(string $table, string $index): bool
    {
        $indexes = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name=?", [$table]);
        foreach ($indexes as $idx) {
            if ($idx->name === $index) {
                return true;
            }
        }
        return false;
    }
};

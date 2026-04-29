<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migration vers système de coordonnées en entiers (cUA)
     *
     * Conversion :
     * - 1 UA = 100 cUA (centi-UA)
     * - 1 AL = 6 324 100 cUA
     *
     * Objectif : Éviter les problèmes d'arrondis avec des décimales
     * en utilisant des entiers pour toutes les positions intra-système
     */
    public function up(): void
    {
        // ÉTAPE 1 : Créer colonnes temporaires pour conversion
        Schema::table('objets_spatiaux', function (Blueprint $table) {
            $table->bigInteger('position_x_cua')->default(0)->after('position_z')
                ->comment('Position X en cUA (centi-UA) - 1 UA = 100 cUA, 1 AL = 6324100 cUA');
            $table->bigInteger('position_y_cua')->default(0)->after('position_x_cua')
                ->comment('Position Y en cUA (centi-UA)');
            $table->bigInteger('position_z_cua')->default(0)->after('position_y_cua')
                ->comment('Position Z en cUA (centi-UA)');
        });

        // ÉTAPE 2 : Convertir les données existantes (AL → cUA)
        // Facteur de conversion : 1 AL = 6 324 100 cUA
        DB::statement('UPDATE objets_spatiaux SET
            position_x_cua = ROUND(position_x * 6324100),
            position_y_cua = ROUND(position_y * 6324100),
            position_z_cua = ROUND(position_z * 6324100)
        ');

        // ÉTAPE 3 : Supprimer anciennes colonnes décimales
        Schema::table('objets_spatiaux', function (Blueprint $table) {
            $table->dropColumn(['position_x', 'position_y', 'position_z']);
        });

        // ÉTAPE 4 : Renommer colonnes cUA → position_x/y/z
        Schema::table('objets_spatiaux', function (Blueprint $table) {
            $table->renameColumn('position_x_cua', 'position_x');
            $table->renameColumn('position_y_cua', 'position_y');
            $table->renameColumn('position_z_cua', 'position_z');
        });

        // ÉTAPE 5 : Mettre à jour planetes (cache positions en cUA)
        Schema::table('planetes', function (Blueprint $table) {
            $table->bigInteger('cache_position_x_cua')->nullable()->after('cache_position_z')
                ->comment('Cache position X en cUA (centi-UA)');
            $table->bigInteger('cache_position_y_cua')->nullable()->after('cache_position_x_cua')
                ->comment('Cache position Y en cUA');
            $table->bigInteger('cache_position_z_cua')->nullable()->after('cache_position_y_cua')
                ->comment('Cache position Z en cUA');
        });

        // Convertir caches existants (UA → cUA)
        DB::statement('UPDATE planetes SET
            cache_position_x_cua = ROUND(cache_position_x * 100),
            cache_position_y_cua = ROUND(cache_position_y * 100),
            cache_position_z_cua = ROUND(cache_position_z * 100)
            WHERE cache_position_x IS NOT NULL
        ');

        // Supprimer anciennes colonnes cache
        Schema::table('planetes', function (Blueprint $table) {
            $table->dropColumn(['cache_position_x', 'cache_position_y', 'cache_position_z']);
        });

        // Renommer
        Schema::table('planetes', function (Blueprint $table) {
            $table->renameColumn('cache_position_x_cua', 'cache_position_x');
            $table->renameColumn('cache_position_y_cua', 'cache_position_y');
            $table->renameColumn('cache_position_z_cua', 'cache_position_z');
        });

        echo "✓ Migration vers cUA (entiers) terminée\n";
        echo "  - objets_spatiaux : position_x/y/z (AL décimal → cUA entier)\n";
        echo "  - planetes : cache_position_x/y/z (UA décimal → cUA entier)\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ÉTAPE 1 : Créer colonnes décimales temporaires
        Schema::table('objets_spatiaux', function (Blueprint $table) {
            $table->decimal('position_x_al', 10, 8)->default(0)->after('position_z');
            $table->decimal('position_y_al', 10, 8)->default(0)->after('position_x_al');
            $table->decimal('position_z_al', 10, 8)->default(0)->after('position_y_al');
        });

        // ÉTAPE 2 : Reconvertir (cUA → AL)
        DB::statement('UPDATE objets_spatiaux SET
            position_x_al = position_x / 6324100,
            position_y_al = position_y / 6324100,
            position_z_al = position_z / 6324100
        ');

        // ÉTAPE 3 : Supprimer colonnes cUA
        Schema::table('objets_spatiaux', function (Blueprint $table) {
            $table->dropColumn(['position_x', 'position_y', 'position_z']);
        });

        // ÉTAPE 4 : Renommer
        Schema::table('objets_spatiaux', function (Blueprint $table) {
            $table->renameColumn('position_x_al', 'position_x');
            $table->renameColumn('position_y_al', 'position_y');
            $table->renameColumn('position_z_al', 'position_z');
        });

        // Planetes
        Schema::table('planetes', function (Blueprint $table) {
            $table->decimal('cache_position_x_ua', 12, 6)->nullable()->after('cache_position_z');
            $table->decimal('cache_position_y_ua', 12, 6)->nullable()->after('cache_position_x_ua');
            $table->decimal('cache_position_z_ua', 12, 6)->nullable()->after('cache_position_y_ua');
        });

        DB::statement('UPDATE planetes SET
            cache_position_x_ua = cache_position_x / 100,
            cache_position_y_ua = cache_position_y / 100,
            cache_position_z_ua = cache_position_z / 100
            WHERE cache_position_x IS NOT NULL
        ');

        Schema::table('planetes', function (Blueprint $table) {
            $table->dropColumn(['cache_position_x', 'cache_position_y', 'cache_position_z']);
        });

        Schema::table('planetes', function (Blueprint $table) {
            $table->renameColumn('cache_position_x_ua', 'cache_position_x');
            $table->renameColumn('cache_position_y_ua', 'cache_position_y');
            $table->renameColumn('cache_position_z_ua', 'cache_position_z');
        });
    }
};

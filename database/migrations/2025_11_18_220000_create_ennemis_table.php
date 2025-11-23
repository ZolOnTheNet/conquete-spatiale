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
        Schema::create('ennemis', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('nom', 100);
            $table->text('description')->nullable();

            // Type d'ennemi
            $table->enum('type', ['pirate', 'drone', 'contrebandier', 'mercenaire', 'militaire'])->default('pirate');
            $table->enum('faction', ['independant', 'cartel', 'empire', 'rebelle', 'alien'])->default('independant');

            // Niveau de difficulte
            $table->integer('niveau')->default(1);
            $table->enum('difficulte', ['facile', 'moyen', 'difficile', 'boss'])->default('moyen');

            // Stats de combat
            $table->integer('coque_max')->default(100);
            $table->integer('bouclier_max')->default(50);
            $table->integer('bouclier_regen')->default(3);
            $table->integer('esquive')->default(10);

            // Armement (stats simplifiees)
            $table->integer('degats_min')->default(5);
            $table->integer('degats_max')->default(15);
            $table->integer('precision')->default(70);
            $table->integer('cadence')->default(1);
            $table->string('type_arme', 20)->default('laser');

            // Resistances
            $table->integer('resistance_laser')->default(0);
            $table->integer('resistance_canon')->default(0);
            $table->integer('resistance_missile')->default(0);
            $table->integer('resistance_plasma')->default(0);
            $table->integer('resistance_emp')->default(0);

            // Comportement IA
            $table->enum('tactique', ['agressif', 'defensif', 'equilibre', 'fuite'])->default('equilibre');
            $table->integer('seuil_fuite')->default(20); // % de coque pour fuir

            // Recompenses
            $table->integer('credits_min')->default(100);
            $table->integer('credits_max')->default(500);
            $table->integer('xp_recompense')->default(50);

            // Spawn
            $table->integer('zone_niveau_min')->default(1);
            $table->integer('zone_niveau_max')->default(10);
            $table->integer('chance_spawn')->default(10); // % de chance d'apparition

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ennemis');
    }
};

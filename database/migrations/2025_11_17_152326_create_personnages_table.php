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
        Schema::create('personnages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compte_id')->constrained('comptes')->onDelete('cascade');
            $table->string('nom');
            $table->string('prenom')->nullable();

            // Traits (Daggerheart)
            $table->integer('agilite')->default(0);
            $table->integer('force')->default(0);
            $table->integer('finesse')->default(0);
            $table->integer('instinct')->default(0);
            $table->integer('presence')->default(0);
            $table->integer('savoir')->default(0);

            // Compétences (16 selon GDD - on stocke les niveaux)
            $table->json('competences')->nullable();

            // XP et progression
            $table->integer('experience')->default(0);
            $table->integer('niveau')->default(1);

            // Jetons Daggerheart
            $table->integer('jetons_hope')->default(0);
            $table->integer('jetons_fear')->default(0);

            // Vaisseau actif
            $table->unsignedBigInteger('vaisseau_actif_id')->nullable();

            // Logs
            $table->json('date_logs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnages');
    }
};

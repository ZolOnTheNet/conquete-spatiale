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
        Schema::create('objets_spatiaux', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('type'); // 'vaisseau', 'base', 'asteroide', etc.

            // Position - Secteur (coordonnées entières)
            $table->integer('secteur_x')->default(0);
            $table->integer('secteur_y')->default(0);
            $table->integer('secteur_z')->default(0);

            // Position réelle (décimales)
            $table->decimal('position_x', 10, 3)->default(0);
            $table->decimal('position_y', 10, 3)->default(0);
            $table->decimal('position_z', 10, 3)->default(0);

            // Index pour recherche rapide par secteur
            $table->index(['secteur_x', 'secteur_y', 'secteur_z'], 'idx_secteur');

            // Hiérarchie
            $table->unsignedBigInteger('contenu_dans')->default(0);
            $table->unsignedBigInteger('secteur_id')->nullable();

            // Propriété
            $table->foreignId('proprietaire_id')->nullable()->constrained('personnages')->onDelete('set null');
            $table->unsignedBigInteger('remorque_par')->nullable();

            // Physique
            $table->decimal('volume', 12, 2)->default(0);
            $table->decimal('masse', 12, 2)->default(0);
            $table->integer('resistance')->default(100);
            $table->integer('coef_dommages')->default(0);

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
        Schema::dropIfExists('objets_spatiaux');
    }
};

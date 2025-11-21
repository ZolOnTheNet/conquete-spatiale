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
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->string('type')->default('spatiogare'); // spatiogare, hub, station-militaire, etc.

            // Localisation
            $table->foreignId('planete_id')->nullable()->constrained('planetes')->onDelete('cascade');
            $table->foreignId('systeme_stellaire_id')->constrained('systemes_stellaires')->onDelete('cascade');

            // Position orbitale (si planète)
            $table->decimal('orbite_rayon_ua', 10, 6)->nullable(); // Distance de la planète en UA
            $table->decimal('orbite_angle', 10, 4)->nullable(); // Angle orbital (0-360°)

            // Caractéristiques
            $table->text('description')->nullable();
            $table->integer('capacite_amarrage')->default(50); // Nombre de vaisseaux
            $table->boolean('commerciale')->default(true); // A un marché
            $table->boolean('industrielle')->default(false); // A des usines
            $table->boolean('militaire')->default(false); // Base militaire

            // Services
            $table->boolean('reparations')->default(true);
            $table->boolean('ravitaillement')->default(true);
            $table->boolean('medical')->default(true);

            // Réputation nécessaire (si applicable)
            $table->foreignId('faction_id')->nullable()->constrained('factions')->onDelete('set null');
            $table->integer('reputation_requise')->default(0);

            // Accessibilité
            $table->boolean('accessible')->default(true);
            $table->text('raison_inaccessible')->nullable(); // "Trop de circulation", etc.

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};

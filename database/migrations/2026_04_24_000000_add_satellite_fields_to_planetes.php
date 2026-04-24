<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planetes', function (Blueprint $table) {
            // Lune = planète avec planete_parente_id non null
            $table->foreignId('planete_parente_id')
                ->nullable()
                ->after('systeme_stellaire_id')
                ->constrained('planetes')
                ->onDelete('cascade');

            $table->enum('categorie', ['planete', 'lune'])
                ->default('planete')
                ->after('planete_parente_id');

            // Distance en UA depuis la planète parente (distance_etoile reste pour les planètes primaires)
            $table->decimal('distance_planete', 10, 6)
                ->nullable()
                ->after('distance_etoile');

            $table->index('planete_parente_id');
            $table->index('categorie');
        });
    }

    public function down(): void
    {
        Schema::table('planetes', function (Blueprint $table) {
            $table->dropForeign(['planete_parente_id']);
            $table->dropIndex(['planete_parente_id']);
            $table->dropIndex(['categorie']);
            $table->dropColumn(['planete_parente_id', 'categorie', 'distance_planete']);
        });
    }
};

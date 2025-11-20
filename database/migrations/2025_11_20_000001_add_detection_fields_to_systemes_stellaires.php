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
        Schema::table('systemes_stellaires', function (Blueprint $table) {
            $table->integer('puissance')->nullable()->after('type_etoile');
            $table->decimal('detectabilite_base', 10, 2)->nullable()->after('puissance');
            $table->boolean('poi_connu')->default(false)->after('detectabilite_base');
        });

        // Calculer la puissance et détectabilité pour les systèmes existants
        $this->calculateDetectability();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('systemes_stellaires', function (Blueprint $table) {
            $table->dropColumn(['puissance', 'detectabilite_base', 'poi_connu']);
        });
    }

    /**
     * Calculer la détectabilité basée sur le type spectral
     */
    protected function calculateDetectability(): void
    {
        // Mapping type spectral → puissance
        $puissances = [
            'O' => [150, 200],
            'B' => [100, 140],
            'A' => [80, 100],
            'F' => [60, 80],
            'G' => [40, 60],
            'K' => [30, 40],
            'M' => [20, 30],
        ];

        $systemes = DB::table('systemes_stellaires')->get();

        foreach ($systemes as $systeme) {
            // Exception pour Sol
            if ($systeme->nom === 'Sol') {
                DB::table('systemes_stellaires')
                    ->where('id', $systeme->id)
                    ->update([
                        'puissance' => 50,
                        'detectabilite_base' => 50.0,
                        'poi_connu' => true,
                    ]);
                continue;
            }

            // Extraire première lettre du type spectral
            $typeClass = strtoupper(substr($systeme->type_etoile ?? 'G', 0, 1));

            if (!isset($puissances[$typeClass])) {
                $typeClass = 'G'; // Défaut
            }

            // Puissance aléatoire dans la plage
            $puissance = rand($puissances[$typeClass][0], $puissances[$typeClass][1]);

            // Formule : detectabilite_base = (200 - puissance) / 3
            $detectabilite = (200 - $puissance) / 3;

            DB::table('systemes_stellaires')
                ->where('id', $systeme->id)
                ->update([
                    'puissance' => $puissance,
                    'detectabilite_base' => round($detectabilite, 2),
                    'poi_connu' => false,
                ]);
        }
    }
};

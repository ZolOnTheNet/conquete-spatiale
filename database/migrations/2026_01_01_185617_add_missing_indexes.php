<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Personnages
        Schema::table('personnages', function (Blueprint $table) {
            if (!$this->hasIndex('personnages', 'personnages_compte_id_index')) {
                $table->index('compte_id');
            }
            if (!$this->hasIndex('personnages', 'personnages_dans_station_id_index')) {
                $table->index('dans_station_id');
            }
            if (!$this->hasIndex('personnages', 'personnages_vaisseau_actif_id_index')) {
                $table->index('vaisseau_actif_id');
            }
        });

        // Vaisseaux
        Schema::table('vaisseaux', function (Blueprint $table) {
            if (!$this->hasIndex('vaisseaux', 'vaisseaux_arrime_a_station_id_index')) {
                $table->index('arrime_a_station_id');
            }
        });

        // Stations
        Schema::table('stations', function (Blueprint $table) {
            if (!$this->hasIndex('stations', 'stations_accessible_index')) {
                $table->index('accessible');
            }
        });

        // Missions
        if (Schema::hasTable('missions')) {
            Schema::table('missions', function (Blueprint $table) {
                if (!$this->hasIndex('missions', 'missions_actif_index')) {
                    $table->index('actif');
                }
            });
        }

        // Gisements
        if (Schema::hasTable('gisements')) {
            Schema::table('gisements', function (Blueprint $table) {
                if (!$this->hasIndex('gisements', 'gisements_decouvert_par_index')) {
                    $table->index('decouvert_par');
                }
            });
        }

        // Mines
        Schema::table('mines', function (Blueprint $table) {
            if (!$this->hasIndex('mines', 'mines_installateur_id_index')) {
                $table->index('installateur_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('personnages', function (Blueprint $table) {
            $table->dropIndex(['compte_id']);
            $table->dropIndex(['dans_station_id']);
            $table->dropIndex(['vaisseau_actif_id']);
        });

        Schema::table('vaisseaux', function (Blueprint $table) {
            $table->dropIndex(['arrime_a_station_id']);
        });

        Schema::table('stations', function (Blueprint $table) {
            $table->dropIndex(['accessible']);
        });

        if (Schema::hasTable('missions')) {
            Schema::table('missions', function (Blueprint $table) {
                $table->dropIndex(['actif']);
            });
        }

        if (Schema::hasTable('gisements')) {
            Schema::table('gisements', function (Blueprint $table) {
                $table->dropIndex(['decouvert_par']);
            });
        }

        Schema::table('mines', function (Blueprint $table) {
            $table->dropIndex(['installateur_id']);
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = Schema::getIndexes($table);
        return collect($indexes)->contains('name', $indexName);
    }
};

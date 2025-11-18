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
            $table->boolean('source_gaia')->default(false)->after('nb_planetes');
            $table->string('gaia_source_id')->nullable()->unique()->after('source_gaia');
            $table->decimal('gaia_ra', 12, 8)->nullable()->after('gaia_source_id'); // Right Ascension
            $table->decimal('gaia_dec', 12, 8)->nullable()->after('gaia_ra'); // Declination
            $table->decimal('gaia_distance_ly', 10, 2)->nullable()->after('gaia_dec');
            $table->decimal('gaia_magnitude', 8, 4)->nullable()->after('gaia_distance_ly');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('systemes_stellaires', function (Blueprint $table) {
            $table->dropColumn([
                'source_gaia',
                'gaia_source_id',
                'gaia_ra',
                'gaia_dec',
                'gaia_distance_ly',
                'gaia_magnitude',
            ]);
        });
    }
};

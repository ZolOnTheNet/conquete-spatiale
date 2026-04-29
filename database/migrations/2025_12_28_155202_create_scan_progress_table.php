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
        Schema::create('scan_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnage_id')->constrained('personnages')->onDelete('cascade');

            // Relation polymorphique vers n'importe quel objet détectable
            $table->string('detectable_type'); // 'App\Models\SystemeStellaire', 'App\Models\Planete', etc.
            $table->unsignedBigInteger('detectable_id');

            $table->decimal('score_detection', 10, 2);
            $table->decimal('cumul_scans', 10, 2)->default(0);
            $table->integer('nb_scans_effectues')->default(0);
            $table->integer('bonus_dice_type')->default(0)->comment('Type de dé de bonus: 0=aucun, 4/6/8/10/12=bonus, -6=malus');

            $table->boolean('detecte')->default(false);

            $table->timestamps();

            // Index
            $table->index(['personnage_id', 'detectable_type', 'detectable_id'], 'idx_scan_progress');
            $table->index(['personnage_id', 'detecte']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scan_progress');
    }
};

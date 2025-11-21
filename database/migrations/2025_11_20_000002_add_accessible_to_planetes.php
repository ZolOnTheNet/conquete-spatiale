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
        Schema::table('planetes', function (Blueprint $table) {
            $table->boolean('accessible')->default(true)->after('population');
            $table->text('raison_inaccessible')->nullable()->after('accessible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planetes', function (Blueprint $table) {
            $table->dropColumn(['accessible', 'raison_inaccessible']);
        });
    }
};

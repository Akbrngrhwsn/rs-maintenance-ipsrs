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
        Schema::table('procurements', function (Blueprint $table) {
            // Add QR code columns for validation at each approval level
            $table->longText('qr_kepala_ruang')->nullable()->after('director_note');
            $table->longText('qr_management')->nullable()->after('qr_kepala_ruang');
            $table->longText('qr_bendahara')->nullable()->after('qr_management');
            $table->longText('qr_direktur')->nullable()->after('qr_bendahara');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procurements', function (Blueprint $table) {
            $table->dropColumn(['qr_kepala_ruang', 'qr_management', 'qr_bendahara', 'qr_direktur']);
        });
    }
};

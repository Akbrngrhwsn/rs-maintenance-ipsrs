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
        Schema::table('app_requests', function (Blueprint $table) {
            // Tambah kolom untuk menyimpan QR code masing-masing role
            if (!Schema::hasColumn('app_requests', 'qr_kepala_ruang')) {
                $table->longText('qr_kepala_ruang')->nullable()->comment('QR Code untuk Kepala Ruang (base64)');
            }
            if (!Schema::hasColumn('app_requests', 'qr_admin_it')) {
                $table->longText('qr_admin_it')->nullable()->comment('QR Code untuk Admin IT (base64)');
            }
            if (!Schema::hasColumn('app_requests', 'qr_management')) {
                $table->longText('qr_management')->nullable()->comment('QR Code untuk Management (base64)');
            }
            if (!Schema::hasColumn('app_requests', 'qr_bendahara')) {
                $table->longText('qr_bendahara')->nullable()->comment('QR Code untuk Bendahara (base64)');
            }
            if (!Schema::hasColumn('app_requests', 'qr_direktur')) {
                $table->longText('qr_direktur')->nullable()->comment('QR Code untuk Direktur (base64)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_requests', function (Blueprint $table) {
            $table->dropColumn([
                'qr_kepala_ruang',
                'qr_admin_it',
                'qr_management',
                'qr_bendahara',
                'qr_direktur'
            ]);
        });
    }
};

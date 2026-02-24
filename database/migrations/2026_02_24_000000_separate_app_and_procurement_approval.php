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
            // Tambahkan catatan_management jika belum ada
            if (!Schema::hasColumn('app_requests', 'catatan_management')) {
                $table->text('catatan_management')->nullable();
            }
            
            // Kolom untuk tracking status persetujuan pengadaan secara terpisah
            if (!Schema::hasColumn('app_requests', 'procurement_approval_status')) {
                $table->string('procurement_approval_status')->default('pending');
            }
            
            // Catatan khusus dari management untuk pengadaan
            if (!Schema::hasColumn('app_requests', 'catatan_management_procurement')) {
                $table->text('catatan_management_procurement')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_requests', function (Blueprint $table) {
            $table->dropColumn(['procurement_approval_status', 'catatan_management_procurement']);
        });
    }
};

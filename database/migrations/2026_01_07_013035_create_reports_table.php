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
    Schema::create('reports', function (Blueprint $table) {
        $table->id();
        // Data Pelapor (User 1 - Public)
        $table->string('pelapor_nama')->nullable(); // Opsional jika anonim
        $table->string('ruangan'); // Dropdown nanti
        $table->text('keluhan'); 
        
        // Sistem Status (Inti Logika)
        // Enum sesuai permintaan Anda
        $table->enum('status', [
            'Belum Diproses', 
            'Diproses', 
            'Selesai', 
            'Tidak Selesai', // Trigger Pengadaan
            'Ditolak' // Tambahan agar sesuai Flowchart jika Admin tidak ACC
        ])->default('Belum Diproses');

        // Data Admin/Teknisi
        $table->text('tindakan_teknisi')->nullable(); // Diisi saat validasi
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};

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
    // Tabel 1: Header Pengajuan (Sesuai Flowchart Manager)
    Schema::create('app_requests', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ID Manager yg request
        $table->string('nama_aplikasi');
        $table->text('deskripsi');
        
        // Status Flowchart:
        // 1. pending_director (Menunggu ACC Direktur)
        // 2. approved (Di-ACC Direktur, masuk ke Admin)
        // 3. rejected (Ditolak Direktur)
        // 4. in_progress (Admin sedang mengerjakan fitur)
        // 5. completed (Selesai semua)
        $table->string('status')->default('pending_director');
        
        $table->text('catatan_direktur')->nullable(); // Alasan tolak/terima
        $table->timestamps();
    });

    // Tabel 2: Detail Fitur (Sesuai Flowchart Admin melengkapi fitur)
    Schema::create('app_features', function (Blueprint $table) {
        $table->id();
        $table->foreignId('app_request_id')->constrained()->onDelete('cascade');
        $table->string('nama_fitur');
        $table->boolean('is_done')->default(false); // Checklist progress
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_requests_tables');
    }
};

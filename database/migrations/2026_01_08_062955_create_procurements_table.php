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
    Schema::create('procurements', function (Blueprint $table) {
        $table->id();
        // Hubungkan ke tabel reports
        $table->foreignId('report_id')->constrained()->onDelete('cascade'); 
        // Simpan daftar barang dalam format JSON
        $table->json('items'); 
        // Status pengadaan: pending, submitted, approved, rejected
        $table->string('status')->default('pending'); 
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurements');
    }
};

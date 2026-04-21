<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('new_item_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Karu yang mengajukan
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');  // Ruangan pengaju
            $table->string('purpose'); // Tujuan pengadaan (cth: Komputer untuk pegawai baru)
            $table->json('items'); // Detail barang beserta jumlahnya
            $table->string('status')->default('pending_admin'); 
            // Status alur: pending_admin -> pending_management -> pending_bendahara -> pending_director -> approved / rejected
            
            // Catatan penolakan
            $table->text('reject_note')->nullable();
            
            // Kolom QR Code persetujuan
            $table->text('qr_admin')->nullable();
            $table->text('qr_management')->nullable();
            $table->text('qr_bendahara')->nullable();
            $table->text('qr_direktur')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('new_item_requests');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // Kita taruh setelah ID, tipe string, harus unik, dan boleh null dulu (untuk data lama)
            $table->string('ticket_number', 30)->after('id')->unique()->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('ticket_number');
        });
    }
};
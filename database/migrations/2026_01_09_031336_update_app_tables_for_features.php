<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah Ticket Number di App Request
        Schema::table('app_requests', function (Blueprint $table) {
            $table->string('ticket_number')->unique()->after('id')->nullable();
        });

        // 2. Tambah Waktu Selesai di Fitur
        Schema::table('app_features', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('is_done');
        });
    }

    public function down(): void
    {
        Schema::table('app_requests', function (Blueprint $table) {
            $table->dropColumn('ticket_number');
        });
        Schema::table('app_features', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
};
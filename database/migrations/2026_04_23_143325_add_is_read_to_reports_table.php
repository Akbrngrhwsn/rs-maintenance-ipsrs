<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
{
    Schema::table('reports', function (Blueprint $table) {
        // Tambahkan kolom is_read_by_admin (default false / belum dibaca)
        $table->boolean('is_read_by_admin')->default(false)->after('status');
    });
}

public function down(): void
{
    Schema::table('reports', function (Blueprint $table) {
        $table->dropColumn('is_read_by_admin');
    });
}
};

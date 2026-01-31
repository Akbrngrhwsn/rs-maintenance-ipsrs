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
        $table->text('catatan_admin')->nullable()->after('catatan_direktur');
    });
}

public function down(): void
{
    Schema::table('app_requests', function (Blueprint $table) {
        $table->dropColumn('catatan_admin');
    });
}
};

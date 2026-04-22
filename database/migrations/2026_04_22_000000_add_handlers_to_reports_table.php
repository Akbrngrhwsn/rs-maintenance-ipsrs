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
        Schema::table('reports', function (Blueprint $table) {
            $table->string('handled_by_admin')->nullable()->comment('Nama admin yang menangani');
            $table->string('handled_by_karu')->nullable()->comment('Nama kepala ruang yang menangani');
            $table->string('handled_by_management')->nullable()->comment('Nama management yang menangani');
            $table->string('handled_by_bendahara')->nullable()->comment('Nama bendahara yang menangani');
            $table->string('handled_by_director')->nullable()->comment('Nama direktur yang menangani');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn([
                'handled_by_admin',
                'handled_by_karu',
                'handled_by_management',
                'handled_by_bendahara',
                'handled_by_director'
            ]);
        });
    }
};

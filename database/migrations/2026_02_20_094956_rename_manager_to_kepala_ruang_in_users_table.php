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
    public function up()
{
    // Mengupdate data yang sudah ada di database
    DB::table('users')->where('role', 'manager')->update(['role' => 'kepala_ruang']);
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
{
    DB::table('users')->where('role', 'kepala_ruang')->update(['role' => 'manager']);
}
};

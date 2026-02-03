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
        Schema::create('i_t_notes', function (Blueprint $col) {
            $col->id();
            $col->text('note'); // Kolom untuk isi catatan
            $col->timestamps(); // Ini otomatis mencatat created_at (tanggal & waktu)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('i_t_notes');
    }
};

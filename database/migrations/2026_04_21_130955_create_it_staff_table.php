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
    Schema::create('it_staffs', function (Blueprint $table) {
        $table->id();
        $table->string('nama');
        $table->boolean('is_on_duty')->default(false); // Untuk menandai siapa yg sedang piket
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('it_staff');
    }
};

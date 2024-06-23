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
        Schema::create('trafico_embarque', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('trafico_id');
            $table->unsignedBigInteger('embarque_id');

            $table->foreign('trafico_id')->references('id')->on('traficos')->onDelete('cascade');
            $table->foreign('embarque_id')->references('id')->on('embarque')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trafico_embarque');
    }
};

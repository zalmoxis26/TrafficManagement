<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('traficos', function (Blueprint $table) {
            $table->unsignedBigInteger('pedimento_id')->nullable();
            $table->foreign('pedimento_id')->references('id')->on('pedimento')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('traficos', function (Blueprint $table) {
            $table->dropForeign(['pedimento_id']);
            $table->dropColumn('pedimento_id');
        });
    }
};

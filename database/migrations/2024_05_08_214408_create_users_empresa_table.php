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
        Schema::create('users_empresa', function (Blueprint $table) {
            $table->id(); // clave primaria
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // clave foránea a la tabla 'users'
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade'); // clave foránea a 'empresas'
            $table->timestamps(); // columnas de timestamp
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_empresa');
    }
};

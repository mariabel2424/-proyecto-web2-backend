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
        Schema::create('deportista_tutores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_deportista');
            $table->unsignedBigInteger('id_usuario');
            $table->string('parentesco', 50)->nullable();
            $table->boolean('es_principal')->default(false);
            $table->boolean('es_emergencia')->default(false);
            $table->timestamps();
            
            $table->foreign('id_deportista')->references('id_deportista')->on('deportistas')->onDelete('cascade');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('cascade');
            
            $table->unique(['id_deportista', 'id_usuario']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deportista_tutores');
    }
};

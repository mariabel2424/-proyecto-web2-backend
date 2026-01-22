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
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id('id_asistencia');
            $table->unsignedBigInteger('id_grupo');
            $table->unsignedBigInteger('id_deportista');
            $table->unsignedBigInteger('id_instructor')->nullable();
            $table->date('fecha');
            $table->enum('estado', ['presente', 'ausente', 'justificado', 'tardanza'])->default('presente');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->foreign('id_grupo')->references('id_grupo')->on('grupos_curso')->onDelete('cascade');
            $table->foreign('id_deportista')->references('id_deportista')->on('deportistas')->onDelete('cascade');
            $table->foreign('id_instructor')->references('id_usuario')->on('usuarios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};

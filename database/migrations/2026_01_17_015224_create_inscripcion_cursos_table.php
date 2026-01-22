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
        Schema::create('inscripcion_cursos', function (Blueprint $table) {
            $table->id('id_inscripcion');
            $table->unsignedBigInteger('id_curso');
            $table->unsignedBigInteger('id_grupo')->nullable();
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_deportista');
            $table->date('fecha_inscripcion');
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['activa', 'completada', 'cancelada'])->default('activa');
            $table->decimal('calificacion', 5, 2)->nullable();
            $table->text('comentarios')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('id_curso')->references('id_curso')->on('cursos')->onDelete('cascade');
            $table->foreign('id_grupo')->references('id_grupo')->on('grupos_curso')->onDelete('set null');
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('cascade');
            $table->foreign('id_deportista')->references('id_deportista')->on('deportistas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscripcion_cursos');
    }
};

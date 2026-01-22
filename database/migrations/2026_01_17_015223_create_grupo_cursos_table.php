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
        Schema::create('grupos_curso', function (Blueprint $table) {
            $table->id('id_grupo');
            $table->unsignedBigInteger('id_curso');
            $table->string('nombre', 100);
            $table->integer('cupo_maximo');
            $table->integer('cupo_actual')->default(0);
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->json('dias_semana')->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'completo'])->default('activo');
            $table->unsignedBigInteger('id_instructor')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('id_curso')->references('id_curso')->on('cursos')->onDelete('cascade');
            $table->foreign('id_instructor')->references('id_usuario')->on('usuarios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo_cursos');
    }
};

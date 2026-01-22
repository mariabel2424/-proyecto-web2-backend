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
        Schema::create('cursos', function (Blueprint $table) {
            $table->id('id_curso');
            $table->string('nombre', 200);
            $table->string('slug', 200)->unique();
            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('representante', 200)->nullable();
            $table->string('email_representante', 100)->nullable();
            $table->string('telefono_representante', 20)->nullable();
            $table->enum('tipo', ['vacacional', 'regular', 'especial'])->default('vacacional');
            $table->enum('estado', ['activo', 'inactivo', 'finalizado'])->default('activo');
            $table->integer('cupo_maximo')->nullable();
            $table->integer('cupo_actual')->default(0);
            $table->decimal('precio', 10, 2)->default(0);
            $table->string('imagen')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};

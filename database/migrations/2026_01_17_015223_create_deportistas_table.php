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
        Schema::create('deportistas', function (Blueprint $table) {
            $table->id('id_deportista');
            $table->unsignedBigInteger('id_categoria')->nullable();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('cedula', 20)->nullable()->unique();
            $table->date('fecha_nacimiento');
            $table->enum('genero', ['masculino', 'femenino', 'otro'])->nullable();
            $table->string('foto')->nullable();
            $table->text('direccion')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->decimal('peso', 5, 2)->nullable();
            $table->decimal('altura', 5, 2)->nullable();
            $table->string('tipo_sangre', 10)->nullable();
            $table->text('alergias')->nullable();
            $table->text('enfermedades')->nullable();
            $table->text('medicamentos')->nullable();
            $table->string('contacto_emergencia', 100)->nullable();
            $table->string('telefono_emergencia', 20)->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('id_categoria')->references('id_categoria')->on('categorias')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deportistas');
    }
};

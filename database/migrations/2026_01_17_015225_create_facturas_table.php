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
        Schema::create('facturas', function (Blueprint $table) {
            $table->id('id_factura');
            $table->unsignedBigInteger('id_deportista')->nullable();
            $table->unsignedBigInteger('id_tutor')->nullable();
            $table->unsignedBigInteger('id_inscripcion')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('numero_factura', 50)->unique();
            $table->string('concepto', 200);
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('impuesto', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->enum('estado', ['pendiente', 'pagada', 'vencida', 'cancelada'])->default('pendiente');
            $table->string('metodo_pago', 50)->nullable();
            $table->string('comprobante_pago')->nullable();
            $table->text('observaciones')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('id_deportista')->references('id_deportista')->on('deportistas')->onDelete('set null');
            $table->foreign('id_tutor')->references('id_usuario')->on('usuarios')->onDelete('set null');
            $table->foreign('id_inscripcion')->references('id_inscripcion')->on('inscripcion_cursos')->onDelete('set null');
            $table->foreign('usuario_id')->references('id_usuario')->on('usuarios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};

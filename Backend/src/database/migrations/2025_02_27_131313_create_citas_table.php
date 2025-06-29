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
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('empleado_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('contrato_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->dateTime('fecha')->nullable();
            $table->enum('estado', ['pendiente', 'cancelada', 'completada']);
            $table->integer('numero_de_atenciones');
            $table->boolean('has_comment')->default(false); // Booleano para evaluar si tiene comentario o no
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};


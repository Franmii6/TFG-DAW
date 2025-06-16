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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string("apellidos");
            $table->string('tlf');
            $table->string('direccion');
            $table->string('municipio');
            $table->string('provincia');
            $table->integer('anos_experiencia')->default(0);
            $table->string('DNI');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};

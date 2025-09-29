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
        Schema::create('evaluacion_cliente', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_Cliente');
            $table->tinyInteger('estado')->default(0)->comment('0: Pendiente , 1: Aceptado , 2: Rechazado');
            $table->string('observaciones');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluacion_cliente');
    }
};

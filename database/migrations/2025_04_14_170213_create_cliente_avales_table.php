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
        Schema::create('avales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_Cliente');
            $table->string('dniAval');
            $table->string('apellidoPaternoAval');
            $table->string('apellidoMaternoAval');
            $table->string('nombresAval');
            $table->integer('telefonoFijoAval');
            $table->integer('telefonoMovilAval');
            $table->integer('direccionAval');
            $table->string('referenciaDomicilioAval');
            $table->string('departamentoAval');
            $table->string('provinciaAval');
            $table->string('distritoAval');
            $table->string('relacionClienteAval');

            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('id_Cliente')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente_avales');
    }
};
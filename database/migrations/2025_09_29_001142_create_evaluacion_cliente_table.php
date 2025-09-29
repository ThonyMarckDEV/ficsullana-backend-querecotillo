<?php

use App\Models\User;
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
            $table->string('producto');
            $table->decimal('monto_prestamo', 10, 2);
            $table->int('tasa_interes');
            $table->int('cuotas');
            $table->string('modalidad_credito');
            $table->string('destino_credito');
            $table->string('periodo_credito');
            $table->tinyInteger('estado')->default(0)->comment('0: Pendiente , 1: Aceptado , 2: Rechazado');
            $table->string('observaciones')->nullable();
            $table->string('')->nullable();
            $table->timestamps();


            $table->foreign('id_Cliente')->references('id')->on('usuarios')->onDelete('cascade');
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

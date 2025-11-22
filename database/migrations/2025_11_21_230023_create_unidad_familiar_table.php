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
        Schema::create('unidad_familiar', function (Blueprint $table) {
            $table->id();
            
            // Relación con la tabla principal de evaluaciones (ajusta el nombre de la tabla si es diferente)
            $table->foreignId('id_Evaluacion')->constrained('evaluacion_cliente')->onDelete('cascade');

            // 1. Número de Miembros que la componen
            $table->integer('numero_miembros')->default(0);

            // 2. Gastos de Alimentación mensual
            $table->decimal('gastos_alimentacion', 10, 2)->default(0.00);

            // 3. Gastos de educación y detalle (niños en etapa escolar/univ)
            $table->decimal('gastos_educacion', 10, 2)->default(0.00);
            $table->text('detalle_educacion')->nullable()->comment('Indicar niños en etapa escolar y/o universidad');

            // 4. Otros gastos de apoyo familiar (Luz, agua, telefonía, otros)
            $table->decimal('gastos_servicios', 10, 2)->default(0.00);

            // 5. Gastos de movilidad
            $table->decimal('gastos_movilidad', 10, 2)->default(0.00);

            // 6. Deudas en otras IFIS (Estructura plana para 3 IFIs como solicitaste)
            $table->boolean('tiene_deudas_ifis')->default(false);
            
            // IFI 01
            $table->string('ifi_1_nombre')->nullable();
            $table->decimal('ifi_1_cuota', 10, 2)->nullable();
            
            // IFI 02
            $table->string('ifi_2_nombre')->nullable();
            $table->decimal('ifi_2_cuota', 10, 2)->nullable();
            
            // IFI 03
            $table->string('ifi_3_nombre')->nullable();
            $table->decimal('ifi_3_cuota', 10, 2)->nullable();

            // 7. Gasto adicional de salud (monto y frecuencia)
            $table->decimal('gastos_salud', 10, 2)->default(0.00);
            $table->string('frecuencia_salud')->nullable()->comment('Ej: Mensual, Semanal, Esporádico');
            $table->text('detalle_salud')->nullable()->comment('Descripción de la situación actual');

            // Totales autocalculados (opcional, pero útil para reportes rápidos)
            $table->decimal('total_gastos_mensuales', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unidad_familiar');
    }
};
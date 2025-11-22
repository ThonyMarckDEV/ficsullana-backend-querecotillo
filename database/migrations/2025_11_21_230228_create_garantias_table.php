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
        Schema::create('garantias', function (Blueprint $table) {
            $table->id();
            
            // Relación con la tabla evaluacion_cliente
            // Asumo que el nombre de tu tabla padre es 'evaluacion_cliente' como pusiste en tu código anterior
            $table->foreignId('id_Evaluacion')->constrained('evaluacion_cliente')->onDelete('cascade');

            // --- EL CAMPO DE LOS CHECKBOXES (Mutuamente excluyentes) ---
            // 1 = Declaración Jurada
            // 0 = Garantías Reales (Anexar Tasación)
            $table->boolean('es_declaracion_jurada')->default(true)->comment('1: Decl. Jurada, 0: Garantía Real');

            // Columnas de datos
            $table->enum('moneda', ['PEN', 'USD'])->default('PEN')->comment('S/. o $');
            $table->string('clase_garantia')->nullable();
            $table->string('documento_garantia')->nullable();
            $table->string('tipo_garantia')->nullable();
            $table->text('descripcion_bien')->nullable();
            $table->string('direccion_bien')->nullable();
            
            // Montos
            $table->decimal('monto_garantia', 15, 2)->default(0.00);
            $table->decimal('valor_comercial', 15, 2)->default(0.00);
            $table->decimal('valor_realizacion', 15, 2)->default(0.00);
            
            // Datos registrales
            $table->string('ficha_registral')->nullable();
            $table->date('fecha_ultima_valuacion')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garantias');
    }
};
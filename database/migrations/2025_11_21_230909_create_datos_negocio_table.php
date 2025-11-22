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
        // 1. TABLA PRINCIPAL (Datos Generales del Negocio)
        Schema::create('datos_negocio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_Evaluacion')->constrained('evaluacion_cliente')->onDelete('cascade');

            // --- SECCIÓN B: DE LOS OTROS INGRESOS ---
            $table->string('otros_ingresos_sector')->nullable()->comment('Sector económico');
            $table->string('otros_ingresos_tiempo')->nullable()->comment('Tiempo en el rubro');
            $table->string('riesgo_sector')->nullable()->comment('Sensibilidad de riesgo');
            $table->decimal('otros_ingresos_monto', 15, 2)->default(0.00);
            $table->string('otros_ingresos_frecuencia')->nullable(); // Mensual, quincenal, etc.
            
            // Dependencia y sustento
            $table->boolean('depende_otros_ingresos')->default(false);
            $table->text('sustento_otros_ingresos')->nullable(); // Puede ser URL de archivo o texto explicativo
            
            // Medios de pago
            $table->boolean('tiene_medios_pago')->default(false);
            $table->string('descripcion_medios_pago')->nullable()->comment('Si marca SI, especificar');

            // --- SECCIÓN C: BALANCE Y RESULTADOS ---
            
            // 1. Ubicación y atención
            $table->string('zona_ubicacion')->nullable();
            $table->string('modalidad_atencion')->nullable();
            $table->text('restriccion_actual')->nullable();

            // 2. Ventas
            $table->decimal('ventas_diarias', 15, 2)->default(0.00);

            // 3. Ahorros
            $table->boolean('cuenta_con_ahorros')->default(false);
            $table->boolean('ahorros_sustentables')->default(false);

            // 4 & 5. Compras
            $table->date('fecha_ultima_compra')->nullable();
            $table->decimal('monto_ultima_compra', 15, 2)->default(0.00);
            $table->string('variacion_compras_mes_anterior')->nullable()->comment('Variación % o monto respecto al mes anterior');

            // (Nota: Las preguntas 6 y 7 van en tablas separadas abajo)

            // 8. Cuentas por cobrar
            $table->decimal('cuentas_por_cobrar_monto', 15, 2)->default(0.00);
            $table->integer('cuentas_por_cobrar_num_clientes')->default(0);
            $table->string('tiempo_recuperacion')->nullable();
            $table->string('foto_apuntes_cobranza')->nullable()->comment('Ruta del archivo/foto');

            // 9. Activo Fijo
            $table->text('detalle_activo_fijo')->nullable();
            $table->decimal('valor_actual_activo_fijo', 15, 2)->default(0.00);
            $table->string('foto_activo_fijo')->nullable();

            // 10. Efectivo Actual
            $table->integer('dias_efectivo')->default(0);
            $table->decimal('monto_efectivo', 15, 2)->default(0.00);

            // 11. Pagos del mes (Cuotas, servicios)
            $table->decimal('pagos_realizados_mes', 15, 2)->default(0.00);

            // 12. Gastos Operativos/Admin
            $table->decimal('gastos_administrativos_fijos', 15, 2)->default(0.00);
            $table->decimal('gastos_operativos_variables', 15, 2)->default(0.00);

            // 13. Mermas / Imprevistos
            $table->decimal('imprevistos_mermas', 15, 2)->default(0.00);

            // 14. Declaraciones / PDT
            $table->decimal('promedio_ventas_pdt', 15, 2)->default(0.00);
            $table->decimal('contribucion_essalud_anual', 15, 2)->default(0.00);

            // 15. Referencias Comerciales
            $table->text('referencias_comerciales')->nullable()->comment('Proveedores, clientes, vecinos');

            $table->timestamps();
        });

        // 2. TABLA DETALLE DE PRODUCTOS (Pregunta 6 y 7)
        // Sirve para listar productos, precios de compra/venta e inventario
        Schema::create('detalle_inventario_negocio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_Datos_Negocio')->constrained('datos_negocio')->onDelete('cascade');
            
            $table->string('nombre_producto');
            $table->string('unidad_medida')->nullable()->comment('Kilos, Unidades, Cajas');
            
            // Pregunta 6: Precios
            $table->decimal('precio_compra_unitario', 10, 2)->default(0.00);
            $table->decimal('precio_venta_unitario', 10, 2)->default(0.00);
            $table->decimal('margen_ganancia', 5, 2)->nullable()->comment('Porcentaje opcional');

            // Pregunta 7: Inventario actual
            $table->decimal('cantidad_inventario', 10, 2)->default(0.00);
            $table->decimal('precio_total_estimado', 15, 2)->default(0.00)->comment('Cantidad * Precio Costo');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_inventario_negocio');
        Schema::dropIfExists('datos_negocio');
    }
};
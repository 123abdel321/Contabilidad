<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('nom_contratos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('id_empleado')->comment('ID del empleado asociado al contrato. Relación con la tabla de empleados');
            $table->integer('id_periodo')->comment('ID del período de nómina asociado. Relación con la tabla de períodos');
            $table->integer('id_concepto_basico')->default(0)->comment('Concepto base para cálculo de salario. Se usa para identificar el salario base y agregar automáticamente horas en el período');
            
            // Fechas del contrato
            $table->date('fecha_inicio_contrato')->comment('Fecha de inicio del contrato laboral');
            $table->date('fecha_fin_contrato')->nullable()->comment('Fecha de finalización del contrato. Null indica contrato a término indefinido');
            $table->date('fecha_inicio_periodo')->nullable()->comment('Copia de dato de inicio del periodo, para poder acceder a este dato más rápido');
            $table->date('fecha_fin_periodo')->nullable()->comment('Copia del dato de fin del periodo, para poder acceder a este dato más rápido');
            
            // Estados y tipos
            $table->integer('estado')->default(1)->comment('Estado del contrato: 0=Inactivo, 1=Activo, 2=Finalizado');
            $table->integer('termino')->default(0)->comment('Tipo de término: 0=Indefinido, 1=Fijo, 2=Obra o labor, 3=Transitorio (según Art. 46 CST)');
            $table->integer('tipo_salario')->default(0)->comment('Tipo de salario: 0=Normal, 1=Honorarios, 2=Integral, 3=Servicios, 4=Practicante');
            $table->integer('tipo_empleado')->default(0)->comment('Tipo de empleado: 0=Administrativo, 1=Operativo, 2=Ventas, 3=Otros');
            
            // Relaciones con otras entidades
            $table->integer('id_centro_costo')->comment('Centro de costo asociado al contrato');
            $table->integer('id_oficio')->nullable()->comment('Oficio o cargo específico del empleado');
            
            // Información salarial
            $table->decimal('salario', 12)->default(0)->comment('Valor del salario base. Para salario integral incluye factores prestacionales');
            
            // Información para PILA (Planilla Integrada de Liquidación de Aportes)
            $table->integer('tipo_cotizante')->default(1)->comment('Tipo cotizante al sistema de seguridad social (Decreto PILA): 1=Dependiente, 12=Aprendiz etapa lectiva, 13=Aprendiz etapa productiva, etc.');
            $table->integer('subtipo_cotizante')->nullable()->default(0)->comment('Subtipo cotizante: 1=Pensionado vejez activo, 3=Pensionado por edad, etc. (Decreto PILA)');
            
            // Fondos de seguridad social
            $table->integer('id_fondo_salud')->nullable()->comment('EPS asociada al empleado');
            $table->integer('id_fondo_pension')->nullable()->comment('Fondo de pensiones asociado');
            $table->integer('id_fondo_cesantias')->nullable()->comment('Fondo de cesantías (si aplica)');
            $table->integer('id_fondo_caja_compensacion')->nullable()->comment('Caja de compensación familiar');
            $table->integer('id_fondo_arl')->nullable()->comment('ARL asociada al empleado');
            
            // Información de riesgos laborales
            $table->integer('nivel_riesgo_arl')->default(1)->comment('Nivel de riesgo ARL: I (1)=Bajo, II (2)=Medio, III (3)=Alto, IV (4)=Muy alto, V (5)=Extremo');
            $table->decimal('porcentaje_arl', 7, 4)->default(0.522)->comment('Porcentaje de cotización ARL según nivel de riesgo: I=0.522%, II=1.044%, III=2.436%, IV=4.35%, V=6.96%');
            
            // Retención en la fuente
            $table->integer('metodo_retencion')->default(0)->comment('Método de retención en la fuente: 0=Mensual, 1=Anual (Art. 383 ET)');
            $table->char('porcentaje_fijo', 10)->nullable()->comment('Porcentaje fijo para retención anual (cuando método=1)');
            $table->char('disminucion_defecto_retencion')->default('0')->comment('Disminución por defecto a la retención (puede modificarse en la colilla de pago)');
            
            // Beneficios y dotación
            $table->boolean('auxilio_transporte')->default(true)->comment('Indica si el empleado recibe auxilio de transporte (según salario mínimo legal vigente)');
            $table->string('talla_camisa', 10)->nullable()->comment('Talla de camisa para dotación');
            $table->string('talla_pantalon', 10)->nullable()->comment('Talla de pantalón para dotación');
            $table->string('talla_zapatos', 10)->nullable()->comment('Talla de zapatos para dotación');
            
            // Auditoría
            $table->integer('created_by')->nullable()->comment('Usuario que creó el registro');
            $table->integer('updated_by')->nullable()->comment('Usuario que actualizó el registro por última vez');
            $table->dateTime('created_at')->useCurrent()->comment('Fecha de creación del registro');
            $table->dateTime('updated_at')->useCurrent()->comment('Fecha de última actualización del registro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nom_contratos');
    }
};

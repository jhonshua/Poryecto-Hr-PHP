<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComprobantesPagosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        cambiarBase('autofacturador');
        Schema::connection('empresa')->table('comprobantes_pagos', function ($table) {

            $table->string('fecha_pago',30)->default('');
            $table->string('monto_total_pagos',30)->default('');
            $table->string('total_traslados_base_iva16',30)->default('');
            $table->string('total_traslados_impuesto_iva16',30)->default('');
            $table->string('imp_saldo_anterior',30);
            $table->string('imp_saldo_insoluto',30);

            $table->text('response_soap')->default('');
            $table->text('response_soap_cancel')->default('');
            $table->text('sello_cdf')->default('');
            $table->string('folio',50)->default('');
            $table->text('cadena_origen')->default('');
            $table->string('uuid',100)->default('');
            $table->string('fecha_timbre',10)->default('');
            $table->text('sello_fiscal')->default('');
            $table->string('estado',2)->default('1');
            $table->text('observaciones');
        });

        cambiarBase('autofacturador02');
        Schema::connection('empresa')->table('comprobantes_pagos', function ($table) {
            $table->string('fecha_pago',30)->default('');
            $table->string('monto_total_pagos',30)->default('');
            $table->string('total_traslados_base_iva16',30)->default('');
            $table->string('total_traslados_impuesto_iva16',30)->default('');
            $table->string('imp_saldo_anterior',30);
            $table->string('imp_saldo_insoluto',30);

            $table->text('response_soap')->default('');
            $table->text('response_soap_cancel')->default('');
            $table->text('sello_cdf')->default('');
            $table->string('folio',50)->default('');
            $table->text('cadena_origen')->default('');
            $table->string('uuid',100)->default('');
            $table->string('fecha_timbre',10)->default('');
            $table->text('sello_fiscal')->default('');
            $table->string('estado',2)->default('1');
            $table->text('observaciones');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        cambiarBase('autofacturador');
        Schema::connection('empresa')->table('comprobantes_pagos', function($table) {
            $table->dropColumn('fecha_pago');
            $table->dropColumn('monto_total_pagos');
            $table->dropColumn('imp_saldo_anterior');
            $table->dropColumn('imp_saldo_insoluto');

            $table->dropColumn('response_soap');
            $table->dropColumn('response_soap_cancel');
            $table->dropColumn('sello_cdf');
            $table->dropColumn('folio');
            $table->dropColumn('cadena_origen');
            $table->dropColumn('uuid');
            $table->dropColumn('fecha_timbre');
            $table->dropColumn('sello_fiscal');
            $table->dropColumn('estado');
        });

        cambiarBase('autofacturador02');
        Schema::connection('empresa')->table('comprobantes_pagos', function($table) {
            $table->dropColumn('fecha_pago');
            $table->dropColumn('monto_total_pagos');
            $table->dropColumn('imp_saldo_anterior');
            $table->dropColumn('imp_saldo_insoluto');

            $table->dropColumn('response_soap');
            $table->dropColumn('response_soap_cancel');
            $table->dropColumn('sello_cdf');
            $table->dropColumn('folio');
            $table->dropColumn('cadena_origen');
            $table->dropColumn('uuid');
            $table->dropColumn('fecha_timbre');
            $table->dropColumn('sello_fiscal');
            $table->dropColumn('estado');
        });
    }
}

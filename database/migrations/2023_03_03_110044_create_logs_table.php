<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        cambiarBase('autofacturador');
        Schema::connection('empresa')->table('logs', function ($table) {
            $table->bigInteger('id_pago');
            $table->text('response_soap');
        });

        cambiarBase('autofacturador02');
        Schema::connection('empresa')->table('logs', function ($table) {
            $table->bigInteger('id_pago');
            $table->text('response_soap');
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
            $table->dropColumn('id_pago');
            $table->dropColumn('response_soap');
        });

        cambiarBase('autofacturador02');
        Schema::connection('empresa')->table('comprobantes_pagos', function($table) {
            $table->dropColumn('id_pago');
            $table->dropColumn('response_soap');
        });
    }
}

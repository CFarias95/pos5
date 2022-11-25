<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenatAddFieldsCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('companies', function (Blueprint $table) {

            $table->boolean('rimpe_emp')->default(false);
            $table->boolean('rimpe_np')->default(false);
            $table->boolean('rise')->default(false);
            $table->boolean('contribuyente_especial')->default(false);
            $table->boolean('obligado_contabilidad')->default(false);
            $table->boolean('agente_retencion')->default(false);
            $table->string('agente_retencion_num')->nullable();
            $table->string('contribuyente_especial_num')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('rimpe_emp');
            $table->dropColumn('rimpe_np');
            $table->dropColumn('rise');
            $table->dropColumn('contribuyente_especial');
            $table->dropColumn('obligado_contabilidad');
            $table->dropColumn('agente_retencion');
            $table->dropColumn('agente_retencion_num');
            $table->dropColumn('contribuyente_especial_num');

        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantAddModulesToAccountant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accountant', function (Blueprint $table) {

            DB::connection('tenant')->table('module_levels')->insert(['value'=>'accounting_reconciliation','description'=>'Punteo contable', 'module_id'=>9]);
            DB::connection('tenant')->table('module_levels')->insert(['value'=>'accounting_audit','description'=>'Auditoria de contabilidad', 'module_id'=>9]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accountant', function (Blueprint $table) {
            //
        });
    }
}

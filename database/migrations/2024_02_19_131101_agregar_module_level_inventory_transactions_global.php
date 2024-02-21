<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AgregarModuleLevelInventoryTransactionsGlobal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('module_levels')->insert([
            'value' => 'inventory_transactions',
            'description' => 'Transacciones',
            'module_id' => 8
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('module_levels')
            ->where('value', 'inventory_transactions')
            ->where('description', 'Transacciones')
            ->where('module_id', 8)
            ->delete();
    }
}

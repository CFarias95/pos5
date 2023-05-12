<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantAddCurrencyTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->table('cat_currency_types')->insert([
            ['id' => 'EUR', 'active' => true, 'symbol' => '€', 'description' => 'Euros'],
            ['id' => 'COP', 'active' => true, 'symbol' => '$$',  'description' => 'Pesos Colombianos'],
            ['id' => 'GTQ', 'active' => true, 'symbol' => 'Q',  'description' => 'Quetzales'],
            ['id' => 'VED', 'active' => true, 'symbol' => 'Bs',  'description' => 'Bolivares'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantProduccionCambioBodegaCantidad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_supplies', function (Blueprint $table) {

            $table->dropColumn('total_producir');
            $table->dropColumn('lugar_produccion');
        });

        Schema::table('items', function (Blueprint $table) {

            $table->integer('total_producir')->nullable();
            $table->integer('lugar_produccion')->nullable();
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
    }
}

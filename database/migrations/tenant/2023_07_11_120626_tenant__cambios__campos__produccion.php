<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantCambiosCamposProduccion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_supplies', function (Blueprint $table) {
            $table->decimal('percentage_decimal', 12, 10)->change()->nullable();
            $table->float('quantity', 15, 10)->change()->nullable();
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

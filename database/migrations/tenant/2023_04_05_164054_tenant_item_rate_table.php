<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantItemRateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('item_rate', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('rate_id');
            //$table->string('unit_type_id');
            //$table->decimal('quantity_unit',12,4);
            $table->decimal('price1', 12, 2);
            //$table->decimal('price2', 12, 2);
            //$table->decimal('price3', 12, 2);
            //$table->foreign('unit_type_id')->references('id')->on('cat_unit_types');
            $table->foreign('rate_id')->references('id')->on('rates');
            $table->foreign('item_id')->references('id')->on('items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('item_rate');
    }
}

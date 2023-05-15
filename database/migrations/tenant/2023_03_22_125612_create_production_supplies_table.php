<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductionSuppliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('production_supplies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('production_name');
            $table->unsignedInteger('production_id')->length(20);
            $table->string('item_supply_name');
            $table->unsignedInteger('item_supply_id')->length(20);
            $table->string('warehouse_name')->nullable();
            $table->unsignedInteger('warehouse_id')->length(20)->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamps();
    
            $table->foreign('production_id')->references('id')->on('production')->onDelete('cascade');
            $table->foreign('item_supply_id')->references('id')->on('item_supplies')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('production_supplies');
    }
}

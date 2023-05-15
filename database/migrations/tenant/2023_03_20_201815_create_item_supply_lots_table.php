<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemSupplyLotsTable extends Migration
{
    public function up()
    {
        Schema::create('item_supply_lots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('item_supply_id')->length(20);
            $table->string('item_supply_name');
            $table->unsignedInteger('lot_id')->length(20);
            $table->string('lot_code');
            $table->unsignedInteger('production_id')->length(20);
            $table->string('production_name');
            $table->integer('quantity');
            $table->date('expiration_date')->nullable();
            $table->timestamps();

            $table->foreign('item_supply_id')->references('id')->on('item_supplies')->onDelete('cascade');
            $table->foreign('lot_id')->references('id')->on('item_lots_group')->onDelete('cascade');
            $table->foreign('production_id')->references('id')->on('production')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('item_supply_lots');
    }
}


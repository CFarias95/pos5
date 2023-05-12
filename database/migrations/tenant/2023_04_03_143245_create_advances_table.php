<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdvancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advances', function (Blueprint $table) {

            $table->increments('id');
            $table->char('idMethodType', 2);
            $table->char('id_payment', 2);
            $table->string('reference', 255)->nullable();
            $table->integer('idCliente')->unsigned()->nullable();
            $table->double('valor', 15, 3);
            $table->boolean('is_supplier')->nullable()->default(false);
            $table->string('observation', 255)->nullable();
            $table->timestamps();

            $table->foreign('idCliente')->references('id')->on('persons');
            $table->foreign('idMethodType')->references('id')->on('payment_method_types');
            $table->foreign('id_payment')->references('id')->on('payment_method_types');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advances');
    }
}

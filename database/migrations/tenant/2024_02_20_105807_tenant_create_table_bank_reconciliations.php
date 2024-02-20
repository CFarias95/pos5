<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantCreateTableBankReconciliations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->create('bank_reconciliations', function ($table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->double('initial_value', 15, 2)->nullable()->default(0);
            $table->double('total_debe', 15, 2)->nullable()->default(0);
            $table->double('total_haber', 15, 2)->nullable()->default(0);
            $table->double('diference_value', 15, 2)->nullable()->default(0);
            $table->tinyInteger('status')->default(0); //0: Creada, 1: Cerrada,
            $table->unsignedInteger('account_id');
            $table->date('month');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('account_id')->references('id')->on('account_movements');

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tenant')->dropIfExists('bank_reconciliations');
    }
}

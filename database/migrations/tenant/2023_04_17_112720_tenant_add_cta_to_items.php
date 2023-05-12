<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddCtaToItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {

            $table->integer('purchase_cta')->unsigned()->nullable();
            $table->integer('sale_cost_cta')->unsigned()->nullable();
            $table->integer('income_cta')->unsigned()->nullable();
            $table->integer('item_process_cta')->unsigned()->nullable();
            $table->integer('item_finish_cta')->unsigned()->nullable();
            $table->integer('item_import_cta')->unsigned()->nullable();

            $table->foreign('purchase_cta')->references('id')->on('account_movements');
            $table->foreign('sale_cost_cta')->references('id')->on('account_movements');
            $table->foreign('income_cta')->references('id')->on('account_movements');
            $table->foreign('item_process_cta')->references('id')->on('account_movements');
            $table->foreign('item_finish_cta')->references('id')->on('account_movements');
            $table->foreign('item_import_cta')->references('id')->on('account_movements');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {

            $table->dropColumn('purchase_cta');
            $table->dropColumn('sale_cost_cta');
            $table->dropColumn('income_cta');
            $table->dropColumn('item_process_cta');
            $table->dropColumn('item_finish_cta');
            $table->dropColumn('item_import_cta');



        });
    }
}

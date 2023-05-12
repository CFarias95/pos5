<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddFieldToConfigurations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {

            $table->integer('cta_charge')->unsigned()->nullable();
            $table->integer('cta_paymnets')->unsigned()->nullable();
            $table->integer('cta_suppliers_advances')->unsigned()->nullable();
            $table->integer('cta_client_advances')->unsigned()->nullable();
            $table->integer('cta_clients')->unsigned()->nullable();
            $table->integer('cta_suppliers')->unsigned()->nullable();
            $table->integer('cta_purchases')->unsigned()->nullable();
            $table->integer('cta_sale_costs')->unsigned()->nullable();
            $table->integer('cta_incomes')->unsigned()->nullable();
            $table->integer('cta_transit_imports')->unsigned()->nullable();
            $table->integer('cta_taxes')->unsigned()->nullable();
            $table->integer('cta_service_taxes')->unsigned()->nullable();
            $table->integer('cta_item_process')->unsigned()->nullable();
            $table->integer('cta_iva_tax')->unsigned()->nullable();
            $table->integer('cta_income_tax')->unsigned()->nullable();

            $table->foreign('cta_charge')->references('id')->on('account_movements');
            $table->foreign('cta_paymnets')->references('id')->on('account_movements');
            $table->foreign('cta_suppliers_advances')->references('id')->on('account_movements');
            $table->foreign('cta_client_advances')->references('id')->on('account_movements');
            $table->foreign('cta_clients')->references('id')->on('account_movements');
            $table->foreign('cta_suppliers')->references('id')->on('account_movements');
            $table->foreign('cta_purchases')->references('id')->on('account_movements');
            $table->foreign('cta_sale_costs')->references('id')->on('account_movements');
            $table->foreign('cta_incomes')->references('id')->on('account_movements');
            $table->foreign('cta_transit_imports')->references('id')->on('account_movements');
            $table->foreign('cta_taxes')->references('id')->on('account_movements');
            $table->foreign('cta_service_taxes')->references('id')->on('account_movements');
            $table->foreign('cta_item_process')->references('id')->on('account_movements');
            $table->foreign('cta_iva_tax')->references('id')->on('account_movements');
            $table->foreign('cta_income_tax')->references('id')->on('account_movements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {

            $table->dropColumn('cta_charge');
            $table->dropColumn('cta_paymnets');
            $table->dropColumn('cta_suppliers_advances');
            $table->dropColumn('cta_client_advances');
            $table->dropColumn('cta_clients');
            $table->dropColumn('cta_suppliers');
            $table->dropColumn('cta_purchases');
            $table->dropColumn('cta_sale_costs');
            $table->dropColumn('cta_incomes');
            $table->dropColumn('cta_transit_imports');
            $table->dropColumn('cta_taxes');
            $table->dropColumn('cta_service_taxes');
            $table->dropColumn('cta_item_process');
            $table->dropColumn('cta_iva_tax');
            $table->dropColumn('cta_income_tax');
        });
    }
}

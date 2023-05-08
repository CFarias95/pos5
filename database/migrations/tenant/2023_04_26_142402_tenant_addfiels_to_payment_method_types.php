<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddfielsToPaymentMethodTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('payment_method_types', function (Blueprint $table) {

            $table->dropColumn('countable_acount');

        });

        Schema::table('payment_method_types', function (Blueprint $table) {

            $table->integer('countable_acount')->unsigned()->nullable();
            $table->integer('countable_acount_payment')->unsigned()->nullable();
            $table->foreign('countable_acount_payment')->references('id')->on('account_movements');
            $table->foreign('countable_acount')->references('id')->on('account_movements');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_method_types', function (Blueprint $table) {
            $table->dropColumn('countable_acount_payment');
        });
    }
}

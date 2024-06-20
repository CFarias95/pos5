<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddSecondFieldTocatAffectationIgvTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cat_affectation_igv_types', function (Blueprint $table) {
            $table->integer('account_sale')->unsigned()->nullable();
            $table->foreign('account_sale')->references('id')->on('account_movements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cat_affectation_igv_types', function (Blueprint $table) {
           $table->dropColumn('account_sale');
        });
    }
}

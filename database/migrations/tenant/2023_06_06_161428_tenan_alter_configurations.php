<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenanAlterConfigurations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {


            $table->integer('cta_item_finish')->unsigned()->nullable();


            $table->foreign('cta_item_finish')->references('id')->on('account_movements');
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


           $table->dropColumn('cta_item_finish');
        });
    }
}

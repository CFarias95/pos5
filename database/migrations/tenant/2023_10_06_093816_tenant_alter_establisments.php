<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAlterEstablisments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('establishments',function(Blueprint $table){
            $table->integer('customer_associate_id')->unsigned()->nullable();
            $table->foreign('customer_associate_id')->references('id')->on('persons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        schema::table('establishments', function(Blueprint $table){
            $table->dropColumn('customer_associate_id');
        });

    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddnumberToFeetables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_fee', function (Blueprint $table) {
            $table->integer('number')->unsigned();
        });
        Schema::table('purchase_fee', function (Blueprint $table) {
            $table->integer('number')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_fee', function (Blueprint $table) {
            $table->dropColumn('number');
        });
        Schema::table('purchase_fee', function (Blueprint $table) {
            $table->dropColumn('number');
        });
    }
}

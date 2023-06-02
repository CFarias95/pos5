<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Tenantagregarcamposts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('technical_services', function (Blueprint $table) {

            $table->boolean('delivered')->nullable();
            $table->boolean('review')->nullable();
            $table->boolean('other')->nullable();
            $table->boolean('solved')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('technical_services', function (Blueprint $table) {
            $table->dropColumn('delivered');
            $table->dropColumn('review');
            $table->dropColumn('other');
            $table->dropColumn('solved');
        });
    }
}

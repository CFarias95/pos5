<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAlterToDispatches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dispatches', function (Blueprint $table) {
            $table->integer('reference_transfer_id')->unsigned()->nullable();
            $table->foreign('reference_transfer_id')->references('id')->on('inventories_transfer')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dispatches', function (Blueprint $table) {
            $table->dropColumn('inventories_transfer');
        });
    }
}

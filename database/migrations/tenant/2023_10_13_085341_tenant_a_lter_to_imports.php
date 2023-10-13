<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantALterToImports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import', function (Blueprint $table) {
            $table->double('isd', 15, 2)->nullable();
            $table->double('comunications', 15, 8)->nullable();
            $table->integer('cta_isd')->unsigned()->nullable();
            $table->integer('cta_comunications')->unsigned()->nullable();

            $table->foreign('cta_isd')->references('id')->on('account_movements')->onDelete('cascade');
            $table->foreign('cta_comunications')->references('id')->on('account_movements')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('import', function (Blueprint $table) {
            $table->dropColumn('isd');
            $table->dropColumn('comunications');
            $table->dropColumn('cta_isd');
            $table->dropColumn('cta_comunications');
        });
    }
}

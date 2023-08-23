<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Altertable2ToProductionSupplies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('production_supplies', function (Blueprint $table) {

            $table->decimal('cost_per_unit', 15, 8)->nullable()->change();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('production_supplies', function (Blueprint $table) {
            //
            $table->dropColumn('cost_per_unit');
        });
    }
}

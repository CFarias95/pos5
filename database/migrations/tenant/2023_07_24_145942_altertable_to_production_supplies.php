<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PhpParser\Node\Expr\Cast\Double;

class AltertableToProductionSupplies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('production_supplies', function (Blueprint $table) {
            $table->decimal('quantity', 15, 8)->nullable()->change();
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
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenanlAddFieldToBankReconciliations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bank_reconciliations', function (Blueprint $table) {
            $table->double('init_value', 15, 4)->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_reconciliations', function (Blueprint $table) {
            $table->dropColumn('init_value');
        });
    }
}

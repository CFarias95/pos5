<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterToAccountingEntryItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_entry_items', function (Blueprint $table) {
            $table->unsignedBigInteger('bank_reconciliation_id')->nullable();
            $table->tinyInteger('bank_reconciliated')->nullable()->default(0);
            $table->dateTime('date_bank_reconciliated')->nullable();
            $table->foreign('bank_reconciliation_id')
                ->references('id')
                ->on('bank_reconciliations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounting_entry_items', function (Blueprint $table) {
            $table->dropColumn('bank_reconciliated');
            $table->dropColumn('bank_reconciliation_id');
            $table->dropColumn('date_bank_reconciliated');
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAlterToaAccountingEntryItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_entry_items', function (Blueprint $table) {

            $table->boolean('reconciliation')->nullable()->default(false);
            $table->boolean('audited')->nullable()->default(false);

            $table->integer('user_id_reconciliation')->unsigned()->nullable();
            $table->integer('user_id_audited')->unsigned()->nullable();

            $table->foreign('user_id_reconciliation')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id_audited')->references('id')->on('users')->onDelete('cascade');

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
            $table->dropColumn('reconciliation');
            $table->dropColumn('audited');
            $table->dropColumn('user_id_reconciliation');
            $table->dropColumn('user_id_audited');
        });
    }
}

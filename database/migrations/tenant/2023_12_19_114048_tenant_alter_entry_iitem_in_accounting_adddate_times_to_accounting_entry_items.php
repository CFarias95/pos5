<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAlterEntryIitemInAccountingAdddateTimesToAccountingEntryItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounting_entry_items', function (Blueprint $table) {
            $table->dateTime('reconciliation_date')->nullable();
            $table->dateTime('audit_date')->nullable();
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
            $table->dropColumn('reconciliation_date');
            $table->dropColumn('audit_date');
        });
    }
}

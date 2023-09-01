<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddRequestInternalToPurchaseQuotations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_quotations', function (Blueprint $table) {
            $table->integer('internal_request')->unsigned()->nullable();
            $table->foreign('internal_request')->references('id')->on('internal_requests')->onDelete('cascade');
        });

        Schema::table('quotations', function (Blueprint $table) {

            $table->integer('internal_request')->unsigned()->nullable();
            $table->foreign('internal_request')->references('id')->on('internal_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_quotations', function (Blueprint $table) {
            $table->dropColumn('internal_request');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('internal_request');
        });
    }
}

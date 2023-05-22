<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddFeeIdToDocumentPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_payments', function (Blueprint $table) {
            $table->integer('fee_id')->unsigned()->nullable();
            $table->foreign('fee_id')->references('id')->on('document_fee')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_payments', function (Blueprint $table) {
            $table->dropColumn('fee_id');
        });
    }
}

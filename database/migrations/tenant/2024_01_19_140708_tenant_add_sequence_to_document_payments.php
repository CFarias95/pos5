<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddSequenceToDocumentPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_payments', function (Blueprint $table) {
            $table->bigInteger('sequential')->nullable();
            $table->string('multipay', 2)->nullable()->default('NO');
        });

        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->bigInteger('sequential')->nullable();
            $table->string('multipay', 2)->nullable()->default('NO');
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
            $table->dropColumn('sequential');
            $table->dropColumn('multipay');
        });

        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->dropColumn('sequential');
            $table->dropColumn('multipay');
        });
    }
}

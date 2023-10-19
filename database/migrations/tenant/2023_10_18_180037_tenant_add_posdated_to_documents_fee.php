<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class TenantAddPosdatedToDocumentsFee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_fee', function (Blueprint $table) {
            $table->date('f_posdated')->nullable()->comment('Fecha posfechado');
            $table->string('posdated')->nullable()->comment('posfechado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_fee', function (Blueprint $table) {
            //
            $table->dropColumn('f_posdated');
            $table->dropColumn('posdated');

        });
    }
}

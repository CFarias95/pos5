<?php

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantImportacionCuentaContable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import', function (Blueprint $table) {

            $table->integer('cuenta_contable')->unsigned()->nullable();
            $table->foreign('cuenta_contable')->references('id')->on('account_movements');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAlterImports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import',function(Blueprint $table ){
            $table->integer('incoterm')->unsigned()->nullable();
            $table->foreign('incoterm')->references('id')->on('incoterms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('import',function(Blueprint $table ){
            $table->dropColumn('incoterm');
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddClavesriDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('documents', function (Blueprint $table) {
            //
            $table->string('clave_SRI')->after('external_id')->default('0');
            $table->date('date_authorization')->after('external_id')->nullable();
            $table->time('time_authorization')->after('date_authorization')->nullable();

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
        Schema::table('documents', function (Blueprint $table) {

            $table->dropColumn('clave_SRI');
            $table->dropColumn('date_authorization');
            $table->dropColumn('time_authorization');
            
        });
    }
}

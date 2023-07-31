<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantAddAprovedToDispatches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dispatches', function (Blueprint $table) {
            $table->boolean('is_aproved')->nullable()->default(true);
            $table->string('response_verification', 255)->nullable();
            $table->string('dateTimeAutorization', 255)->nullable();
            $table->string('response_verification_msg', 255)->nullable();
            $table->boolean('verificated')->nullable();
        });

        DB::connection('tenant')->table('state_types')->insert([['id'=>'02','description'=>'FIRMADO'],['id'=>'04','description'=>'Error']]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dispatches', function (Blueprint $table) {
            $table->dropColumn('is_aproved');
            $table->dropColumn('response_verification');
            $table->dropColumn('dateTimeAutorization');
            $table->dropColumn('response_verification_msg');
            $table->dropColumn('verificated');
        });
    }
}

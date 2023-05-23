<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAgregarObservacionYTareaFinalizada extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('technical_services', function (Blueprint $table) {

            $table->boolean('finalized')->nullable();
            $table->string('observation', 255)->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('technical_services', function (Blueprint $table) {
            $table->dropColumn('finalized');
            $table->dropColumn('observation');

        });
    }
}

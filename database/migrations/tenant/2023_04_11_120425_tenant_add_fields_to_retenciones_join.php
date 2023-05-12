<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddFieldsToRetencionesJoin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('retenciones_join', function (Blueprint $table) {

            $table->string('filename', 255)->nullable()->after('claveAcceso');
            $table->string('external_id', 255)->nullable()->after('filename');
            $table->longText('barCode')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('retenciones_join', function (Blueprint $table) {
            $table->dropColumn('filename');
            $table->dropColumn('external_id');
            $table->dropColumn('barCode');
        });
    }
}

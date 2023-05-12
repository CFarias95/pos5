<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddClaveAccesoToRetencionesJoin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('retenciones_join', function (Blueprint $table) {
            $table->string('claveAcceso', 255)->nullable();
            $table->char('status_id', 2)->nullable()->default('01');
            $table->boolean('verificated')->nullable()->default(false);
            $table->string('response_envio', 255)->nullable();
            $table->string('response_verification', 255)->nullable();
            $table->json('response_message_envio')->nullable();
            $table->json('response_message_verification')->nullable();
            $table->dateTime('DateTimeAutorization')->nullable();

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
            $table->dropColumn('claveAcceso');
            $table->dropColumn('status_id');
            $table->dropColumn('verificated');
            $table->dropColumn('response_envio');
            $table->dropColumn('response_verification');
            $table->dropColumn('response_message_envio');
            $table->dropColumn('response_message_verification');
            $table->dropColumn('DateTimeAutorization');
        });
    }
}

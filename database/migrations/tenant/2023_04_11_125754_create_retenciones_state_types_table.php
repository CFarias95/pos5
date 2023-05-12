<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateRetencionesStateTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retenciones_state_types', function (Blueprint $table) {
            $table->increments('id');
            $table->char('idEstado', 2);
            $table->index('idEstado');
            $table->string('name', 255);
            $table->timestamps();
        });

        DB::connection('tenant')->table('retenciones_state_types')->insert([
            ['idEstado'=>'01','name'=>'CREADA'],
            ['idEstado'=>'02','name'=>'GENERADA'],
            ['idEstado'=>'03','name'=>'RECIBIDA'],
            ['idEstado'=>'04','name'=>'DEVUELTA'],
            ['idEstado'=>'05','name'=>'AUTORIZADA'],
            ['idEstado'=>'06','name'=>'NOAUTORIZADA'],
            ['idEstado'=>'07','name'=>'RECHAZADA'],
            ['idEstado'=>'08','name'=>'ENPROCESO'],
            ['idEstado'=>'09','name'=>'ERROR'],

        ]);

        schema::table('retenciones_join', function(Blueprint $table){

            $table->foreign('status_id')->references('idEstado')->on('retenciones_state_types');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('retenciones_state_types');
    }
}

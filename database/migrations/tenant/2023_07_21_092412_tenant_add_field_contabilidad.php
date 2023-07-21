<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantAddFieldContabilidad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->table('import_concepts')->insert(['description'=>'Comunicaciones']);
        DB::connection('tenant')->table('cat_purchase_document_types')->insert(['id'=>'376','active'=>1,'short'=>'CO','description'=>'Documento no tributario']);
        DB::connection('tenant')->table('codigos_sustento')->insert(['codSustento'=>'xx','description'=>'No tributario','idTipoComprobante'=>'376']);

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

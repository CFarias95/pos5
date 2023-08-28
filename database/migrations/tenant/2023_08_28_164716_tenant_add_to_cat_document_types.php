<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantAddToCatDocumentTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        ;

        DB::connection('tenant')->table('cat_document_types')->insert([

            ['id'=>'11','active'=>0,'short'=>'PJ','description'=>'Pasajes expedidos por empresas de aviación'],
            ['id'=>'12','active'=>0,'short'=>'DF','description'=>'Documentos emitidos por instituciones financieras'],
            ['id'=>'15','active'=>0,'short'=>'VE','description'=>'Comprobante de venta emitido en el Exterior'],
            ['id'=>'16','active'=>0,'short'=>'FD','description'=>'Formulario Único de Exportación(FUE) o Declaración Aduanera Única (DAU) o Declaración Andina de Valor (DAV)'],
            ['id'=>'18','active'=>0,'short'=>'DA','description'=>'Documentos autorizados utilizados en ventas excepto N/C N/D'],
            ['id'=>'19','active'=>0,'short'=>'CP','description'=>'Comprobantes de Pago de Cuotas o Aportes'],
            ['id'=>'21','active'=>0,'short'=>'CA','description'=>'Carta de Porte Aéreo'],
            ['id'=>'22','active'=>0,'short'=>'RP','description'=>'RECAP'],
            ['id'=>'41','active'=>0,'short'=>'CR','description'=>'Comprobante de venta emitido por reembolso'],
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cat_document_types', function (Blueprint $table) {
            //
        });
    }
}

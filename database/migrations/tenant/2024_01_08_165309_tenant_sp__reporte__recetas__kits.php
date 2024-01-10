<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantSpReporteRecetasKits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_Recetas_Kits";
        $sqlCreate = "CREATE PROCEDURE `SP_Recetas_Kits`()
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN
        
        SELECT  it.internal_id as Codigo_interno, CONCAT(it.name,'/',  it.description) as Nombre, 
        it2.internal_id as Codigointerno_insumo ,CONCAT(it2.name,'/', it2.description ) Nombre_insumo,
        CAST(p.quantity AS decimal(16,6)) as Cantidad_consumo
        -- *
        --  p.id, p.item_id , p.individual_item_id , p.quantity  
        from item_supplies AS p
        LEFT JOIN items AS it ON p.item_id = it.id
        LEFT JOIN items AS it2 ON p.individual_item_id = it2.id
         ;
         
        END";

        DB::connection('tenant')->statement($sqlDelete);
        DB::connection('tenant')->statement($sqlCreate);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_Recetas_Kits";
        DB::connection('tenant')->statement($sqlDelete);
    }
}

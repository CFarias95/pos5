<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantCreateSPBaseparaIsd extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //borrar SP
        $sql_delete = "
            DROP PROCEDURE IF EXISTS SP_BaseparaIsd;
        ";
        DB::connection('tenant')->statement($sql_delete);

        //CREA SP
        $sql_create = "CREATE PROCEDURE `SP_BaseparaIsd`(
            IN `id` INT
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT 'Devuelve el valor base para el ca'
        BEGIN

                set @importacion = id ;

                SET @fob = ((SELECT CASE WHEN SUM(b.total_value) IS NULL then 0 ELSE SUM(b.total_value) END AS fob
                    FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                    inner JOIN items AS c ON b.item_id = c.id
                    WHERE a.import_id =  @importacion
                    AND c.unit_type_id <> 'ZZ'
                    AND a.tipo_doc_id = 1 ))  ;

                SET @interes = ((SELECT  CASE WHEN SUM(b.quantity*b.unit_value) IS NULL THEN 0 ELSE SUM(b.quantity*b.unit_value) END AS interes
                    FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                    INNER JOIN items AS c ON b.Item_id = c.id
                    WHERE  a.import_id =  @importacion
                    AND c.concept_id = 8 ))  ;

              SELECT ( @fob + @interes ) AS Importe ;

        END";
        DB::connection('tenant')->statement($sql_create);
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

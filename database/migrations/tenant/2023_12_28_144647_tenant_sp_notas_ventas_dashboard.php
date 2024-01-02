<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantSpNotasVentasDashboard extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_NotasVentasDashboard";
        $sqlCreate = "CREATE PROCEDURE `SP_NotasVentasDashboard`(
            IN `fini` DATE,
            IN `ffin` DATE
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN
        SELECT CASE WHEN ct.name IS NULL THEN 'Sin categoria' ELSE ct.name END AS Categoria , sum(a.cantidad) AS cantidad, SUM(a.total) AS  total
        FROM (
        SELECT di.quantity AS cantidad,  (di.quantity * di.unit_value) AS total , di.item_id ,
        CASE WHEN category_id_array IS NULL THEN 0 ELSE
        ( CASE WHEN category_id_array LIKE '%,%' THEN  SUBSTRING(category_id_array , 2, POSITION( ',' IN category_id_array)-2)  ELSE 0 END )
          END AS idc
         FROM documents AS d INNER JOIN
         document_items AS di ON d.id = di.document_id
         LEFT JOIN items AS i ON  di.item_id = i.id
        WHERE d.date_of_issue >= fini
        AND d.date_of_issue <= ffin

        ) AS a LEFT JOIN categories AS  ct ON a.idc = ct.id
        GROUP BY ct.name
         ;

        END
        ";

        DB::connection('tenant')->statement($sqlDelete);
        //DB::connection('tenant')->statement($sqlCreate);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_NotasVentasDashboard";
        DB::connection('tenant')->statement($sqlDelete);
    }
}

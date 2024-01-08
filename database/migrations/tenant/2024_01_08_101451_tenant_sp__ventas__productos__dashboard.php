<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantSpVentasProductosDashboard extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_Ventas_Productos";
        $sqlCreate = "CREATE PROCEDURE `SP_Ventas_Productos`(
	        IN `fini` DATE,
	        IN `ffin` DATE
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY INVOKER
        COMMENT ''
        BEGIN

        SELECT CONCAT( i.name, CASE WHEN i.description IS NULL THEN '' ELSE CONCAT('/',i.description) END ) Producto , ( b.quantity * b.unit_value ) total
        FROM documents as a INNER JOIN document_items AS b ON a.id = b.document_id
        LEFT JOIN items AS i ON b.item_id = i.id
        WHERE a.date_of_issue >= fini
        and a.date_of_issue <= ffin;

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
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_Ventas_Productos";
        DB::connection('tenant')->statement($sqlDelete);
    }
}

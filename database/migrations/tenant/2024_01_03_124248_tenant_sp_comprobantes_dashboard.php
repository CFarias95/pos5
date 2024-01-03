<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantSpComprobantesDashboard extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_VentasVendedor";
        $sqlCreate = "
        CREATE PROCEDURE `SP_VentasVendedor`(
            IN `fini` DATE,
            IN `ffin` DATE
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY INVOKER
        COMMENT ''
        BEGIN
        
        SELECT u.name as vendedor, SUM(di.quantity) AS cantidad,  SUM(di.quantity * di.unit_value) AS total 
        
         FROM documents AS d INNER JOIN 
         document_items AS di ON d.id = di.document_id
         LEFT JOIN users AS u ON d.seller_id = u.id
        WHERE d.date_of_issue >= fini
        AND d.date_of_issue <= ffin 
         GROUP BY u.name;
         
        END
        ";

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
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_VentasVendedor";
        DB::connection('tenant')->statement($sqlDelete);
    }
}

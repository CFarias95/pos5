<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class Tenantreportcomprasbaseimpuestos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql_create = "
        CREATE PROCEDURE `SP_ComprasBaseImpuestos`(
            IN `date_start` DATE,
            IN `date_end` DATE
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        SELECT a.document_type_id Tipodocinterno, b.description Tipodocumento, b.DocumentTypeID Codtipodocsri , c.description Tipodocsri  ,
                        a.series AS Serieinterna , a.number AS Numerointerno,
                        a.sequential_number secuencial, a.auth_number Numautorizacion,
                        d.name Nombreproveedor, d.number as CIRUC, e.description Tipo,
                        a.date_of_issue as fechadoducmento,
                        a.total_taxed AS Baseiva12, a.total_unaffected AS Baseiva0, a.total_igv Totaliva,  a.total_value Baseimponible, a.total
                        FROM purchases AS a LEFT JOIN cat_purchase_document_types2 AS  b ON a.document_type_id = b.idtYpe
                        left JOIN  cat_purchase_document_types AS c ON b.DocumentTypeID = c.id
                        LEFT JOIN persons AS d ON a.supplier_id   = d.id
                        LEFT JOIN cat_identity_document_types AS e ON d.identity_document_type_id = e.id
        ";
        DB::connection('tenant')->statement($sql_create);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql_delete = "
            DROP PROCEDURE SP_ComprasBaseImpuestos;
        ";
        DB::connection('tenant')->statement($sql_delete);
    }
}

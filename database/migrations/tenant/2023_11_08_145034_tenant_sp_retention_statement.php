<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantSpRetentionStatement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_retention_statement";
        $sqlCreate = "CREATE PROCEDURE `SP_retention_statement`(
            IN `desde` VARCHAR(50),
            IN `hasta` VARCHAR(50),
            IN `supplier_id` INT,
            IN `import_id` INT
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN
        set @desde = desde ;
        set @hasta = hasta ;

        select *
        from (
        select
        p.id AS ID, document_type_id ,
        pty.description as DOCUMENTOOINTERNO,
         series AS SERIE,p.number AS NUMERO, p.sequential_number as factura,  p.supplier_id AS IDP, pr.number as RUC, pr.name as NOMPROVEEDOR,
        'Renta' Tipo_ret,  cr.code Codido_retencion , cr.description Retencion, CAST((unit_value * quantity ) as decimal(12,4)) as base_retencion,
        cast(income_retention as decimaL(12,4)) Importe_retenido
        from purchases as p inner join  purchase_items as pi on p.id = pi.purchase_id
        LEFT JOIN cat_purchase_document_types2 as pty on p.document_type_intern = pty.idtype
        LEFT JOIN import as  i on CASE WHEN pi.IMPORT IS NULL THEN p.import_id ELSE pi.IMPORT END = i.id
        LEFT JOIN persons as pr on p.supplier_id = pr.id
        left join items as it on pi.item_id = it.id
        left join tipo_doc_purchase as tyi on p.tipo_doc_id = tyi.id
        left join cat_retention_types as cr on pi.retention_type_id_income = cr.id
        where p.date_of_issue >= @desde
        and  p.date_of_issue <= @hasta
        AND (0 = supplier_id OR pr.id = supplier_id)
        AND (0 = import_id OR i.id = import_id)
        and pi.retention_type_id_income IS NOT NULL
        UNION ALL
        select
        p.id AS ID, document_type_id ,
        pty.description as DOCUMENTOOINTERNO,
         series AS SERIE,p.number AS NUMERO, p.sequential_number as factura, p.supplier_id AS IDP, pr.number as RUC, pr.name as NOMPROVEEDOR,
        'Iva' Tipo_ret,  cr.code Codido_retencion , cr.description Retencion, CAST( pi.total_igv as decimal(12,4)) as base_retencion,
        cast(iva_retention as decimaL(12,4)) Importe_retenido
        from purchases as p inner join  purchase_items as pi on p.id = pi.purchase_id
        LEFT JOIN cat_purchase_document_types2 as pty on p.document_type_intern = pty.idtype
        LEFT JOIN import as  i on CASE WHEN pi.IMPORT IS NULL THEN p.import_id ELSE pi.IMPORT END = i.id
        LEFT JOIN persons as pr on p.supplier_id = pr.id
        left join items as it on pi.item_id = it.id
        left join tipo_doc_purchase as tyi on p.tipo_doc_id = tyi.id
        left join cat_retention_types as cr on pi.retention_type_id_iva = cr.id
        where p.date_of_issue >= @desde
        and  p.date_of_issue <= @hasta
        and pi.retention_type_id_iva IS NOT NULL
        AND (0 = supplier_id OR pr.id = supplier_id)
        AND (0 = import_id OR i.id = import_id)

        ) as a
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
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_retention_statement";
        DB::connection('tenant')->statement($sqlDelete);
    }
}

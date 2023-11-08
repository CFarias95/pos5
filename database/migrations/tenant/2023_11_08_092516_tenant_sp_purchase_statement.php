<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantSpPurchaseStatement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_purchase_statement";
        $sqlCreate = "CREATE PROCEDURE `SP_purchase_statement`(
            IN `desde` VARCHAR(50),
            IN `hasta` VARCHAR(50),
            IN `supplier_id` INT,
            IN `import_id` INT
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT 'Procedimiento para reporte de extracto de compras'
        BEGIN
                set @desde = desde;
                set @hasta = hasta;

                select *
                from (
                select
                p.id AS ID, document_type_id ,
                pty.description AS DOCUMENTOOINTERNO,
                 series AS SERIE,p.number AS NUMERO, p.date_of_issue AS fecha, p.supplier_id AS IDP, pr.number as RUC, pr.name as NOMPROVEEDOR,
                 p.total_unaffected BASE0, p.total_taxed AS BASEIVA,    p.total_igv IVA, p.total AS TOTAL,
                 dc.name AS departamento , sc.name AS seccion , cc.name as categoria, fc.name as familia,
                 pi.item_id AS IDITEM, it.name as NOMBRE, it.description as DESCRIPCION, pi.quantity CANTIDAD,
                 pi.unit_value as PRECIO, (pi.quantity * pi.unit_value ) AS TOTALLINEA,  pi.total_igv as VALORIVA, pi.percentage_igv AS PORCENTAJEIVA,
                pi.lot_code CODIGOLOTE, pi.date_of_due FECHACADUCIDAD,
                  exchange_rate_sale TCAMBIO,   sequential_number SECUENCIAL , auth_number NUMEROAUTORIZACION,
                codSustento CODIGOSUSTENTO,
                pi.retention_type_id_income RETRENTA , income_retention VALORRETRENTA,
                retention_type_id_iva RETIVA ,iva_retention VALORRETEIVA ,
                CASE WHEN pi.IMPORT IS NULL THEN p.import_id ELSE pi.IMPORT END AS IDIMPORTACION ,
                i.numeroImportacion IMPORTACION, tyi.description As TIPODOCIMPORTACION, ic.description CONCEPTOIMP
                from purchases as p inner join  purchase_items as pi on p.id = pi.purchase_id
                LEFT JOIN cat_purchase_document_types2 as pty on p.document_type_intern = pty.idtype
                LEFT JOIN import as  i on CASE WHEN pi.IMPORT IS NULL THEN p.import_id ELSE pi.IMPORT END = i.id
                LEFT JOIN persons as pr on p.supplier_id = pr.id
                left join items as it on pi.item_id = it.id
                left join import_concepts as ic on it.concept_id = ic.id
                left join tipo_doc_purchase as tyi on p.tipo_doc_id = tyi.id
                inner join (select id,
                  LTRIM(RTRIM(REPLACE(REPLACE(SUBSTRING_INDEX(category_id_array, ',', 1),'[',''),']',''))) as departamento ,
                    LTRIM(RTRIM(REPLACE(REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(category_id_array, ',', 2),',',-1),'[',''),']',''))) as categoria,
                    LTRIM(RTRIM(REPLACE(REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(category_id_array, ',', 3),',',-1),'[',''),']',''))) as seccion,
                    LTRIM(RTRIM(REPLACE(REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(category_id_array, ',', 4),',',-1),'[',''),']',''))) as familia
                 from items as a ) as itc on it.id = itc.id
                left join categories as  dc on itc.departamento = dc.id
                left join categories as  cc on itc.categoria = cc.id
                left join categories as  sc on itc.seccion = sc.id
                left join categories as  fc on itc.familia = fc.id
                where p.date_of_issue >= @desde
                and  p.date_of_issue <= @hasta
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
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_purchase_statement";
        DB::connection('tenant')->statement($sqlDelete);
    }
}

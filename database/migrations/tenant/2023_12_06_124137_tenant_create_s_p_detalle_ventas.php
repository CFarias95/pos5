<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantCreateSPDetalleVentas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sqlDelete="DROP PROCEDURE IF EXISTS SP_ReporteVentasDetalle";
        $sqlCreate = "CREATE PROCEDURE `SP_ReporteVentasDetalle`(
            IN `desde` VARCHAR(50),
            IN `hasta` VARCHAR(50),
            IN `customer_id` INT
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN

             select *
             from (
             select
             p.id AS ID, document_type_id ,

             SUBSTRING( series ,2,10) AS SERIE,p.number AS NUMERO, p.date_of_issue AS fecha, p.customer_id AS IDP, pr.number as RUC, pr.name as NOMCLIENTE,
              p.total_unaffected BASE0, p.total_taxed AS BASEIVA,    p.total_igv IVA, p.total AS TOTAL,
              dc.name AS departamento , sc.name AS seccion , cc.name as categoria, fc.name as familia,
              pi.item_id AS IDITEM, it.name as NOMBRE, it.description as DESCRIPCION, pi.quantity CANTIDAD,
              pi.unit_value as PRECIO, (pi.quantity * pi.unit_value ) AS TOTALLINEA, 0 AS Costo, 0 AS Costototal,  pi.total_igv as VALORIVA, pi.percentage_igv AS PORCENTAJEIVA
            from documents as p inner join  document_items as pi on p.id = pi.document_id
             LEFT JOIN persons as pr on p.customer_id = pr.id
             left join items as it on pi.item_id = it.id
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
             where p.date_of_issue >= desde
             and  p.date_of_issue <= hasta
             AND (0 = customer_id OR pr.id = customer_id)
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
        $sqlDelete="DROP PROCEDURE IF EXISTS SP_ReporteVentasDetalle";
        DB::connection('tenant')->statement($sqlDelete);
    }
}

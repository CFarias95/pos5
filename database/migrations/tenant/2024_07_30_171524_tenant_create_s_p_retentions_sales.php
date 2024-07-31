<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantCreateSPRetentionsSales extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_retentions_customers";
        DB::connection('tenant')->statement($sqlDelete);

        $sqlCreate = "CREATE PROCEDURE `SP_retentions_customers`(
            IN `customer_id` INT,
            IN `mes` VARCHAR(50),
            IN `type_ret` VARCHAR(50)
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT 'SP para recuperar las retenciones de clientes'
        BEGIN
            SELECT CONCAT(SUBSTR(rt.observations,12,2),'/',SUBSTR(rt.observations,14,2),'/',SUBSTR(rt.observations,16,4)) as FechaEmision, rt.date_of_issue AS FechaCarga, CONCAT(rt.series,rt.number) AS Secuencial,
            CONCAT(persons.number,'-') AS Identificacion, persons.name AS Cliente, CONCAT(rt.ubl_version,'-') AS Retencion,rt2.tipo,CAST(rt2.porcentajeRetener AS DECIMAL(4,2)) AS porcentajeRetener, CAST(rt2.baseImponible AS DECIMAL(12,2)) AS baseImponible, CAST(rt2.valorRetenido AS DECIMAL(12,2)) AS valorRetenido
            FROM retentions AS rt
            JOIN persons ON persons.id = rt.supplier_id
            JOIN (
            SELECT id,
                    JSON_UNQUOTE(JSON_EXTRACT(retentions.optional, CONCAT('$[', idx, '].tipo'))) AS tipo,
                    JSON_UNQUOTE(JSON_EXTRACT(retentions.optional, CONCAT('$[', idx, '].codigo'))) AS codigo,
                    JSON_UNQUOTE(JSON_EXTRACT(retentions.optional, CONCAT('$[', idx, '].baseImponible'))) AS baseImponible,
                    JSON_UNQUOTE(JSON_EXTRACT(retentions.optional, CONCAT('$[', idx, '].valorRetenido'))) AS valorRetenido,
                    JSON_UNQUOTE(JSON_EXTRACT(retentions.optional, CONCAT('$[', idx, '].porcentajeRetener'))) AS porcentajeRetener
                FROM
                    retentions,
                    -- Generar números de índice basados en el tamaño del array JSON
                    (SELECT 0 AS idx UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8) AS indices
                WHERE
                    idx < JSON_LENGTH(retentions.optional)
            ) AS rt2 ON rt2.id = rt.id
            WHERE (SUBSTR(rt.observations,14,6) = mes OR mes = '00')
            AND (persons.id = customer_id OR customer_id = 0)
            AND (rt2.tipo = type_ret OR type_ret = 'TODOS');
        END";

        DB::connection('tenant')->statement($sqlCreate);
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

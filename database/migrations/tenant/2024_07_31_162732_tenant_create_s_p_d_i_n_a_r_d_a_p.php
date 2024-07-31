<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantCreateSPDINARDAP extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_Dinardap";
        DB::connection('tenant')->statement($sqlDelete);

        $sqlCreate = <<< EOF
        CREATE PROCEDURE `SP_Dinardap`(
            IN `desde` VARCHAR(50),
            IN `hasta` VARCHAR(50),
            IN `fechacorte` VARCHAR(50)
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN

            SET @desde  = desde ;
            SET @hasta  = hasta ;
            SET @fechacorte  = fechacorte;

            SELECT
            'SR02822' AS 1_codigo_entidad,
            date_format(@hasta, "%d/%m/%Y")  AS 2_fechadatos,
            CASE WHEN p.identity_document_type_id = 1 THEN 'C' WHEN p.identity_document_type_id = 6 THEN 'R' ELSE 'E' END AS 3_Tipoident ,
            p.number AS 4_Iden_sujeto,
            p.name AS 5_Nombre_sujeto,
            CASE WHEN p.identity_document_type_id = 1 THEN 'N' WHEN p.identity_document_type_id = 6 THEN 'J' ELSE 'J' END AS 6_Clase_sujeto,
            p.department_id  AS 7_Provincia ,
            SUBSTRING(p.province_id,3,2)  AS 8_Canton,
            SUBSTRING(p.district_id,5,2) AS 9_PARROQUIA,
            CASE WHEN p.identity_document_type_id = 6 THEN '' ELSE 'M' END AS 10_SEXO, -- revisar ya que no se tiene este campo en el sistema
            CASE WHEN p.identity_document_type_id = 6 THEN '' ELSE 'S' END AS 11_ESTADO_CIVIL, -- revisar ya que no se tiene este campo en el sistema
            CASE WHEN p.identity_document_type_id = 6 THEN '' ELSE 'V' END AS 12_ORIGEN_INGRESOS, -- revisar ya que no se tiene este campo en el sistema
            IF(LENGTH(d.clave_SRI) > 15,SUBSTRING(d.clave_SRI, 25,15),d.clave_SRI) AS 13_NUMOPERCAION,
            CAST(df.amount AS DECIMAL(13,2)) AS 14_VALOROPERACION ,
            IF(CAST(df.amount - IFNULL(pp.val,0)  AS DECIMAL(13,2)) IS NULL , CAST(0.00 AS DECIMAL(13,2)), CAST(df.amount - IFNULL(pp.val,0)  AS DECIMAL(13,2))) AS 15_SALDO_OPERACION,
            DATE_FORMAT(d.date_of_issue , "%d/%m/%Y")  AS 16_FECHA_CONSECION,
            DATE_FORMAT(df.date , "%d/%m/%Y") AS 17_FECHA_VENCIMIENTO,
            DATE_FORMAT( DATE_ADD(df.date, INTERVAL 1 DAY) , "%d/%m/%Y")  AS 18_FECHA_EXIGIBLE,
            DATEDIFF(df.date , d.date_of_issue) AS 19_PLAZO_OPERACION,
            pm.number_days AS 20_PERIODICIDAD_PAGO,
            -- DATEDIFF( @hasta,df.date  ) AS diasvencido,
            -- (df.amount - IFNULL(pp.val,0)) AS pendiente,
            CASE WHEN (df.amount - IFNULL(pp.val,0)) > 0 AND DATEDIFF( @hasta,df.date  ) > 0 THEN DATEDIFF( @hasta,df.date  ) ELSE 0 END AS 21_DIAS_MOROCIDAD,
            CASE WHEN (df.amount - IFNULL(pp.val,0)) > 0 AND DATEDIFF( @hasta,df.date  ) > 0 THEN (df.amount - IFNULL(pp.val,0)) ELSE CAST(0.0 AS DECIMAL(13,2)) END AS 22_MONTO_MOROSIDAD,
            CAST(0.00 AS DECIMAL(13,2)) AS 23_MONTOINTERES_MORA,
            -- DATE_ADD( @hasta, INTERVAL 1 DAY ) as 1d, DATE_ADD( @hasta, INTERVAL 30 DAY ) AS 2d,
            CASE WHEN df.date BETWEEN DATE_ADD( @hasta, INTERVAL 1 DAY ) AND DATE_ADD( @hasta, INTERVAL 30 DAY ) THEN (df.amount - IFNULL(pp.val,0)) ELSE CAST(0.0 AS DECIMAL(13,2)) END AS 24_VALOR_VENCER_1_30_DIAS,
            CASE WHEN df.date BETWEEN DATE_ADD( @hasta, INTERVAL 31 DAY ) AND DATE_ADD( @hasta, INTERVAL 90 DAY ) THEN (df.amount - IFNULL(pp.val,0)) ELSE CAST(0.0 AS DECIMAL(13,2)) END AS 25_VALOR_VENCER_31_90_DIAS,
            CASE WHEN df.date BETWEEN DATE_ADD( @hasta, INTERVAL 91 DAY ) AND DATE_ADD( @hasta, INTERVAL 180 DAY ) THEN (df.amount - IFNULL(pp.val,0)) ELSE CAST(0.0 AS DECIMAL(13,2)) END AS 26_VALOR_VENCER_91_180_DIAS,
            CASE WHEN df.date BETWEEN DATE_ADD( @hasta, INTERVAL 181 DAY ) AND DATE_ADD( @hasta, INTERVAL 360 DAY ) THEN (df.amount - IFNULL(pp.val,0)) ELSE CAST(0.0 AS DECIMAL(13,2)) END AS 27_VALOR_VENCER_181_360_DIAS,
            CASE WHEN df.date > DATE_ADD( @hasta, INTERVAL  360 DAY ) THEN (df.amount- IFNULL(pp.val,0)) ELSE CAST(0.0 AS DECIMAL(13,2)) END AS 28_VALOR_VENCER_MAS_360_DIAS,
            CASE WHEN (df.amount - IFNULL(pp.val,0)) > 0 AND DATEDIFF( @hasta,df.date  ) BETWEEN 1 AND 30 THEN (df.amount - IFNULL(pp.val,0)) ELSE CAST(0.0 AS DECIMAL(13,2)) END AS 29_VALOR_VENCIDO_1_30_DIAS,
            CASE WHEN (df.amount - IFNULL(pp.val,0)) > 0 AND DATEDIFF( @hasta,df.date  ) BETWEEN 31 AND 90 THEN (df.amount - IFNULL(pp.val,0)) ELSE CAST(0.0 AS DECIMAL(13,2)) END AS 30_VALOR_VENCIDO_31_90_DIAS,
            CASE WHEN (df.amount - IFNULL(pp.val,0)) > 0 AND DATEDIFF( @hasta,df.date  ) BETWEEN 91 AND 180 THEN (df.amount - IFNULL(pp.val,0)) ELSE CAST(0.0 AS DECIMAL(13,2)) END AS 31_VALOR_VENCIDO_91_180_DIAS,
            CASE WHEN (df.amount - IFNULL(pp.val,0)) > 0 AND DATEDIFF( @hasta,df.date  ) BETWEEN 181 AND 360 THEN (df.amount - IFNULL(pp.val,0)) ELSE CAST(0.0 AS DECIMAL(13,2)) END AS 32_VALOR_VENCIDO_181_360_DIAS,
            CASE WHEN (df.amount - IFNULL(pp.val,0)) > 0 AND DATEDIFF( @hasta,df.date  ) > 360 THEN (df.amount - IFNULL(pp.val,0)) ELSE CAST(0.0 AS DECIMAL(13,2)) END AS 33_VALOR_VENCIDO_MAS_360_DIAS,
            CAST(0.0 AS DECIMAL(13,2)) AS 34_VALOR_EN_DEMANDA_JUDICIAL,
            CAST(0.0 AS DECIMAL(13,2)) AS 35_CARTERA_CASTIGADA,
            CAST(0.0 AS DECIMAL(13,2)) AS 36_CUOTA_DEL_CREDITO,
            '' AS 37_FECHA_CANCELACION,
            'C' AS 38_FORMA_CANCELACION



            FROM documents AS d
            LEFT JOIN document_fee AS df ON d.id = df.document_id
            JOIN persons AS p ON d.customer_id = p.id
            LEFT JOIN payment_method_types AS pm ON df.payment_method_type_id = pm.id
            LEFT JOIN
            (SELECT  fee_id ,IFNULL(SUM(payment),0) AS val  FROM document_payments  GROUP BY fee_id) AS pp ON df.id = pp.fee_id
            WHERE
            -- d.date_of_issue >= @desde
            -- AND
            d.date_of_issue <= @hasta
            AND df.amount > 69
            AND d.state_type_id NOT IN ('11')
            AND (df.amount - IFNULL(pp.val,0)) > 0;
            END
        EOF;
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

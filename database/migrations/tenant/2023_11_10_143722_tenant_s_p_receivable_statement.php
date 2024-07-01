<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantSPReceivableStatement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_receivable_statement";
        $sqlCreate = "CREATE PROCEDURE `SP_receivable_statement`(
	IN `fecha` VARCHAR(50),
	IN `fecha_fin` VARCHAR(50),
	IN `codcliente` INT,
	IN `codvendedor` INT,
	IN `paid` INT,
	IN `to_pay` DOUBLE
)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN
    SET @fecha_corte = fecha;
    SET @fecha_fin = fecha_fin;
    SET @codcliente = codcliente;
    SET @codvendedor = codvendedor;

    SELECT aa.serie, aa.numero, aa.factura, bb.sequential, aa.vendedor, aa.id_cliente,aa.cliente, aa.identificacion,
           aa.num_cuota,
           aa.tipo_vencimiento,
           aa.valor_vencimiento, aa.fecha_vencimiento,
           CASE WHEN aa.dias_vencido IS NULL OR aa.dias_vencido < 0 THEN 0 ELSE aa.dias_vencido END AS dias_vencidos,
           CASE WHEN bb.pagado IS NULL THEN CAST(0.0 AS DECIMAL(12, 2)) ELSE bb.pagado END AS pagado,
           aa.valor_vencimiento - CASE WHEN bb.pagado IS NULL THEN CAST(0.0 AS DECIMAL(12, 2)) ELSE bb.pagado END AS pendiente
    FROM (
        SELECT a.id AS id_vencimiento, a.document_id AS id_documento, b.series AS serie, b.number AS numero, a.number AS num_cuota,
               us.name AS vendedor, IF(b.series LIKE '%B%',b.clave_SRI, SUBSTRING(b.clave_sri, 25, 15)) factura,
               p.name AS cliente, p.number AS identificacion, p.id AS id_cliente,
               b.payment_condition_id, c.name AS condicion_pago,
               CASE WHEN d.description IS NULL THEN 'Credito' ELSE d.description END AS tipo_vencimiento, a.amount AS valor_vencimiento,
               a.date fecha_vencimiento, DATEDIFF(CAST(@fecha_corte AS DATE), a.date) AS dias_vencido
        FROM document_fee AS a
        INNER JOIN documents AS b ON a.document_id = b.id
        LEFT JOIN (SELECT id, name FROM payment_conditions GROUP BY id, name) AS c ON b.payment_condition_id = c.id
        LEFT JOIN (SELECT id, description FROM payment_method_types GROUP BY id, description) AS d ON a.payment_method_type_id = d.id
        LEFT JOIN persons AS p ON b.customer_id = p.id
        LEFT JOIN establishments AS es ON b.establishment_id = es.id
        LEFT JOIN users AS us ON b.seller_id = us.id
        WHERE b.payment_condition_id IN ('02', '03')
        AND (b.customer_id = @codcliente OR 0 = @codcliente)
        AND (b.seller_id = @codvendedor OR 0 = @codvendedor)
        AND (
        CASE
            WHEN @fecha_corte IS NOT NULL AND @fecha_fin IS NOT NULL THEN
                a.date BETWEEN @fecha_corte AND @fecha_fin
            ELSE
                a.date <= @fecha_corte
        END
    		)
    ) AS aa
    LEFT JOIN (
        SELECT a.document_id, a.fee_id, SUM(a.payment) AS pagado, a.sequential AS sequential
        FROM document_payments AS a
        INNER JOIN documents AS b ON a.document_id = b.id
        WHERE b.payment_condition_id IN ('02', '03')

        GROUP BY a.document_id, a.fee_id, a.sequential
    ) AS bb ON aa.id_documento = bb.document_id AND aa.id_vencimiento = bb.fee_id
    WHERE (aa.valor_vencimiento - CASE WHEN bb.pagado IS NULL THEN CAST(0.0 AS DECIMAL(12, 2)) ELSE bb.pagado END = to_pay || 0 = to_pay)
	 AND  IF(paid = 0 ,(aa.valor_vencimiento - ifnull(bb.pagado,0)) <> 0 , 0=0);
END ";
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
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_receivable_statement";
        DB::connection('tenant')->statement($sqlDelete);
    }
}

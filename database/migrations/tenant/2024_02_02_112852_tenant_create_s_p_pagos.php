<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantCreateSPPagos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS `SP_pagos`;";
        DB::connection('tenant')->statement($sqlDelete);

        $sqlCreate = "CREATE PROCEDURE `SP_pagos`(
            IN `supplier_id` INT,
            IN `date_start` VARCHAR(50),
            IN `date_end` VARCHAR(50),
            IN `multpay` VARCHAR(2)
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN
            IF multpay = 'NO' THEN
            SELECT pp.id, CONCAT('PC',pp.id) AS CODE,
            pp.sequential AS EgresoN, IFNULL(pp.reference,'') AS referencia, pp.date_of_payment AS fechaPago,
            pp.multipay AS multipago, ps.name AS proveedor, p.series AS serie, p.number AS numero,  REPLACE(p.sequential_number,'-','') AS secuencial, cpdt.description AS tipo,
            IFNULL(pf.number,'N/A') AS cuota,
            pm.description AS formaDePago, p.total AS totalDocumento, IFNULL(pf.amount,pp.payment) AS totalCuota,
            CASE WHEN cpdt.`sign` > 0 then pp.payment ELSE pp.payment*-1 END AS totalPagado
            FROM purchase_payments AS pp
            LEFT JOIN purchase_fee AS pf ON pf.purchase_id = pp.purchase_id AND pf.id = pp.fee_id
            JOIN purchases AS p ON  p.id = pp.purchase_id
            JOIN persons AS ps ON ps.id = p.supplier_id
            JOIN payment_method_types AS pm ON pm.id = pp.payment_method_type_id
            JOIN cat_purchase_document_types2 AS cpdt ON cpdt.idType = p.document_type_intern
            WHERE (ps.id = supplier_id OR 0 = supplier_id)
            AND pp.date_of_payment BETWEEN date_start AND date_end;

            ELSE

            SELECT pp.id, CONCAT('PC',pp.id) AS CODE,
            pp.sequential AS EgresoN, IFNULL(pp.reference,'') AS referencia, pp.date_of_payment AS fechaPago,
            pp.multipay AS multipago, ps.name AS proveedor, p.series AS serie, p.number AS numero,  REPLACE(p.sequential_number,'-','') AS secuencial, cpdt.description AS tipo,
            IFNULL(pf.number,'N/A') AS cuota,
            pm.description AS formaDePago, p.total AS totalDocumento, IFNULL(pf.amount,pp.payment) AS totalCuota,
            CASE WHEN cpdt.`sign` > 0 then pp.payment ELSE pp.payment*-1 END AS totalPagado
            FROM purchase_payments AS pp
            LEFT JOIN purchase_fee AS pf ON pf.purchase_id = pp.purchase_id AND pf.id = pp.fee_id
            JOIN purchases AS p ON  p.id = pp.purchase_id
            JOIN persons AS ps ON ps.id = p.supplier_id
            JOIN payment_method_types AS pm ON pm.id = pp.payment_method_type_id
            JOIN cat_purchase_document_types2 AS cpdt ON cpdt.idType = p.document_type_intern
            WHERE (ps.id = supplier_id OR 0 = supplier_id)
            AND pp.date_of_payment BETWEEN date_start AND date_end
            AND pp.multipay = 'NO'
            UNION ALL
            SELECT 'N/A' AS id, 'N/A' AS CODE,
            pp.sequential AS EgresoN, IFNULL(pp.reference,'') AS referencia, pp.date_of_payment AS fechaPago,
            pp.multipay AS multipago, 'N/A' AS proveedor, 'N/A' AS serie, 'N/A' AS numero,  'N/A' AS secuencial, 'N/A' AS tipo,
            'N/A' AS cuota,
            pm.description AS formaDePago, 'N/A' AS totalDocumento, IFNULL(SUM(pf.amount),SUM(pp.payment)) AS totalCuota,
            CASE WHEN cpdt.`sign` > 0 then SUM(pp.payment) ELSE SUM(pp.payment*-1) END AS totalPagado
            FROM purchase_payments AS pp
            LEFT JOIN purchase_fee AS pf ON pf.purchase_id = pp.purchase_id AND pf.id = pp.fee_id
            JOIN purchases AS p ON  p.id = pp.purchase_id
            JOIN persons AS ps ON ps.id = p.supplier_id
            JOIN payment_method_types AS pm ON pm.id = pp.payment_method_type_id
            JOIN cat_purchase_document_types2 AS cpdt ON cpdt.idType = p.document_type_intern
            WHERE pp.multipay = 'SI'
            AND (ps.id = supplier_id OR 0 = supplier_id)
            AND pp.date_of_payment BETWEEN date_start AND date_end
            GROUP BY pp.reference, pp.sequential, pp.multipay, pm.description, pp.date_of_payment, cpdt.`sign`;
            END IF;
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
        $sqlDelete = "DROP PROCEDURE IF EXISTS `SP_pagos`;";
        DB::connection('tenant')->statement($sqlDelete);
    }
}

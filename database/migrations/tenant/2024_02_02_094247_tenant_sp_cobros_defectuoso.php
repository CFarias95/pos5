<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantSpCobrosDefectuoso extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_Cobros_Defectuosos";
        DB::connection('tenant')->statement($sqlDelete);

        $sqlCREATE = "
            CREATE PROCEDURE `SP_Cobros_Defectuosos`(
                IN `client_id` INT,
                IN `desde` DATE,
                IN `hasta` DATE
            )
            LANGUAGE SQL
            NOT DETERMINISTIC
            CONTAINS SQL
            SQL SECURITY DEFINER
            COMMENT ''
            BEGIN
            
            SET @c = client_id ;
            SET @d = desde ;
            SET @h = hasta ;
            
            SELECT 
    dp.id,
    CONCAT('CF', dp.id) AS CODE,
    CASE 
        WHEN dp.multipay = 'SI' THEN CONCAT('Multipago ', dp.sequential)
        ELSE CONCAT('Pago ', dp.sequential)
    END AS secuencial,
    dp.REFERENCE AS Referencia,
    dp.date_of_payment AS fecha_pago,
    CASE 
        WHEN dp.multipay = 'SI' THEN 'Si'
        ELSE 'No'
    END AS Multipago,
    p.name AS Cliente,
    d.series AS Serie,
    d.number AS Numero,
    df.number AS cuota,
    pm.description AS MetodoPago,
    d.total AS TotalDocumento,
    df.amount AS TotalCuota,
    dp.payment AS Valor_pagado,
    df.date AS fecha_vencimiento,
    ae.filename AS AsientoContable
FROM 
    document_payments AS dp
LEFT JOIN 
    document_fee AS df ON df.document_id = dp.document_id AND df.id = dp.fee_id
LEFT JOIN 
    documents AS d ON d.id = dp.document_id
LEFT JOIN 
    persons AS p ON p.id = d.customer_id
LEFT JOIN 
    payment_method_types AS pm ON pm.id = dp.payment_method_type_id
LEFT JOIN 
    accounting_entries AS ae ON ae.document_id LIKE CONCAT('%CF', dp.id, '%')
WHERE 
    d.payment_condition_id IN ('02', '03') AND d.state_type_id <> 11 
    AND dp.date_of_payment >= @d
    AND dp.date_of_payment <= @h
    AND (0 = @c OR d.customer_id = @c)

UNION ALL

SELECT 
    dp.id,
    CONCAT('CF', dp.id) AS CODE,
    CASE 
        WHEN dp.multipay = 'SI' THEN CONCAT('Multipago ', dp.sequential)
        ELSE CONCAT('Pago ', dp.sequential)
    END AS secuencial,
    dp.REFERENCE AS Referencia,
    dp.date_of_payment AS fecha_pago,
    CASE 
        WHEN dp.multipay = 'SI' THEN 'Si'
        ELSE 'No'
    END AS Multipago,
    p.name AS Cliente,
    d.series AS Serie,
    d.number AS Numero,
    0 AS cuota,
    pm.description AS MetodoPago,
    d.total AS TotalDocumento,
    d.total AS TotalCuota,
    dp.payment AS Valor_pagado,
    NULL AS fecha_vencimiento,
    ae.filename AS AsientoContable
FROM 
    document_payments AS dp
LEFT JOIN 
    documents AS d ON d.id = dp.document_id 
LEFT JOIN 
    persons AS p ON p.id = d.customer_id
LEFT JOIN 
    payment_method_types AS pm ON pm.id = dp.payment_method_type_id
LEFT JOIN 
    accounting_entries AS ae ON ae.document_id LIKE CONCAT('%CF', dp.id, '%')
WHERE 
    d.payment_condition_id IN ('01') AND d.state_type_id <> 11 
    AND dp.date_of_payment >= @d
    AND dp.date_of_payment <= @h
    AND (0 = @c OR d.customer_id = @c)

UNION ALL

SELECT 
    d.id,
    CONCAT('CF', d.id) AS CODE,
    0 AS secuencial,
    'Nota crédito' AS Referencia,
    d.date_of_issue AS fecha_pago,
    'No' AS Multipago,
    p.name AS Cliente,
    d.series AS Serie,
    d.number AS Numero,
    0 AS cuota,
    'Nota crédito' AS MetodoPago,
    -1 * d.total AS TotalDocumento,
    -1 * d.total AS TotalCuota,
    0 AS Valor_pagado,
    NULL AS fecha_vencimiento,
    ae.filename AS AsientoContable
FROM  
    documents AS d 
LEFT JOIN 
    persons AS p ON p.id = d.customer_id
INNER JOIN 
    notes AS n ON n.document_id = d.id
LEFT JOIN 
    accounting_entries AS ae ON ae.document_id LIKE CONCAT('%CF', d.id, '%')
WHERE  
    d.state_type_id <> 11 
    AND d.date_of_issue >= @d
    AND d.date_of_issue <= @h
    AND (0 = @c OR d.customer_id = @c);

            
            END
        ";
        DB::connection('tenant')->statement($sqlCREATE);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_Cobros_Defectuosos";
        DB::connection('tenant')->statement($sqlDelete);
    }
}
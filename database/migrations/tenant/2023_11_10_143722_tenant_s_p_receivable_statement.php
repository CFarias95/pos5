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
            IN `fecha` VARCHAR(50)
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN
        
                set @fecha_corte = fecha;
                set @codcliente = codcliente;
                set @codvendedor = codvendedor;
        
                select aa.id_vencimiento, aa.id_documento, aa.serie , aa.numero , aa.factura, aa.vendedor, aa.cliente, aa.identificacion,  aa.condicion_pago, aa.tipo_vencimiento,
                 aa.valor_vencimiento, aa.fecha_vencimiento, CASE WHEN  aa.dias_vencido IS NULL OR aa.dias_vencido <0 THEN 0 ELSE aa.dias_vencido END AS dias_vencidos  ,
                 CASE WHEN bb.pagado IS NULL THEN cast(0.0 as decimal(12,2)) ELSE bb.pagado end as pagado,
                 aa.valor_vencimiento - case when bb.pagado IS NULL THEN cast(0.0 as decimal(12,2)) else bb.pagado end as pendiente
                from (
                select a.id as id_vencimiento, a.document_id as id_documento, b.series as serie , b.number as numero , us.name AS vendedor,  SUBSTRING(b.clave_sri,25,15) factura,
                 p.name as cliente, p.number as identificacion,
                  b.payment_condition_id, c.name as condicion_pago ,
                  case when  d.description IS NULL  THEN 'Credito' else   d.description end as tipo_vencimiento, a.amount as valor_vencimiento,
                  a.date fecha_vencimiento, DATEDIFF (cast(@fecha_corte as date ), a.date ) as dias_vencido
                  from document_fee  as a
                  inner join documents as b on a.document_id = b.id
                  left join (select id, name from payment_conditions  group by id, name ) as c on  b.payment_condition_id = c.id
                  left join (select id, description from payment_method_types GROUP BY id, description ) as d on a.payment_method_type_id = d.id
                  left join persons as p on b.customer_id = p.id
                  left join establishments as es on b.establishment_id = es.id
                  LEFT JOIN users AS us ON b.seller_id = us.id
                  where b.payment_condition_id IN ('02','03')
                  AND ( b.customer_id = @codcliente OR 0 = @codcliente   )
                  AND ( b.seller_id = @codvendedor OR 0 = @codvendedor   )
                ) as aa left join
                ( select   a.document_id, a.fee_id ,   sum(a.payment) as pagado
                 from document_payments  as a inner join documents as b on a.document_id = b.id
                 where b.payment_condition_id IN ('02','03')
                 and a.date_of_payment <= @fecha_corte
                 GROUP by a.document_id, a.fee_id ) as bb on aa.id_documento = bb.document_id and aa.id_vencimiento = bb.fee_id
                 WHERE  (  aa.valor_vencimiento - case when bb.pagado IS NULL THEN cast(0.0 as decimal(12,2)) else bb.pagado end  )  <> 0
        
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
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_receivable_statement";
        DB::connection('tenant')->statement($sqlDelete);
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantCreateSPCollectStatement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_toCollect_statement";
        $sqlCreate = "CREATE PROCEDURE `SP_toCollect_statement`(
            IN `fecha` VARCHAR(50)
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN

        set @fecha_corte = fecha;


        Select id_documento, cliente, identificacion, serie, numero, factura
         ,
         CAST(SUM( case when dias_vencidos = 0 then pendiente else 0 end  ) as decimal(12,2))  as por_vencer ,
        Cast(Sum( case when dias_vencidos >= 1  and  dias_vencidos <= 30 then pendiente else Cast( 0.0 as decimal(12,2)) end  ) as decimal(12,2)) as 1_30_dias ,
        CAST(Sum( case when dias_vencidos >= 1  and  dias_vencidos <= 30 then pendiente else Cast( 0.0 as decimal(12,2)) end  ) as decimal(12,2)) as 1_30_dias ,
        CAST(Sum( case when dias_vencidos >= 31  and  dias_vencidos <= 60 then pendiente else Cast( 0.0 as decimal(12,2)) end  ) as decimal(12,2)) as 31_60_dias ,
        CAST(Sum( case when dias_vencidos >= 61  and  dias_vencidos <= 90 then pendiente else Cast( 0.0 as decimal(12,2)) end  ) as decimal(12,2)) as 61_90_dias ,
        CAST(Sum( case when dias_vencidos >= 91  then pendiente else 0 end  ) as decimal(12,2))  as mas_90_dias,
        Sum(  pendiente  ) as total
        FROM (

        select aa.id_vencimiento, aa.id_documento, aa.serie , aa.numero , aa.factura, aa.cliente, aa.identificacion,  aa.condicion_pago, aa.tipo_vencimiento,
         aa.valor_vencimiento, aa.fecha_vencimiento, CASE WHEN  aa.dias_vencido IS NULL OR aa.dias_vencido <0 THEN 0 ELSE aa.dias_vencido END AS dias_vencidos  ,
         CASE WHEN bb.pagado IS NULL THEN cast(0.0 as decimal(12,2)) ELSE bb.pagado end as pagado,
         aa.valor_vencimiento - case when bb.pagado IS NULL THEN cast(0.0 as decimal(12,2)) else bb.pagado end as pendiente
        from (
        select a.id as id_vencimiento, a.document_id as id_documento, b.series as serie , b.number as numero ,   SUBSTRING(b.clave_sri,25,15) factura,
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
          where b.payment_condition_id IN ('02','03')

        ) as aa left join
        ( select   a.document_id, a.fee_id ,   sum(a.payment) as pagado
         from document_payments  as a inner join documents as b on a.document_id = b.id
         where b.payment_condition_id IN ('02','03')
         and a.date_of_payment <= @fecha_corte
         GROUP by a.document_id, a.fee_id ) as bb on aa.id_documento = bb.document_id and aa.id_vencimiento = bb.fee_id
         WHERE  (  aa.valor_vencimiento - case when bb.pagado IS NULL THEN cast(0.0 as decimal(12,2)) else bb.pagado end  )  <> 0
         ) as b
         WHERE Sum(  pendiente  ) <> 0
        group By id_documento,  serie, numero, factura, cliente, identificacion
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
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_toCollect_statement";
        DB::connection('tenant')->statement($sqlDelete);

    }
}

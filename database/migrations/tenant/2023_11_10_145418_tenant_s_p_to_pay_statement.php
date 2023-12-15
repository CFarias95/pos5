<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantSPToPayStatement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_toPay_statement";
        $sqlCreate = "CREATE PROCEDURE `SP_toPay_statement`(
            IN `fechacorte` VARCHAR(50),
            IN `agrupado` INT,
            IN `codproveedor` INT
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN
         
                   --  set @fecha_corte = fecha;
                       DECLARE sum_null DECIMAL(10,2) DEFAULT 0;
                      SELECT agrupado INTO sum_null ;
                         IF sum_null = 0 THEN
                        Select id_documento, proveedor, identificacion , fecha, secuencial 
                     ,
                     CAST(SUM( case when dias_vencidos = 0 then pendiente else 0 end  ) as decimal(12,2))  as por_vencer ,
                    Cast(Sum( case when dias_vencidos >= 1  and  dias_vencidos <= 30 then pendiente else Cast( 0.0 as decimal(12,2)) end  ) as decimal(12,2)) as 1_30_dias ,
                    CAST(Sum( case when dias_vencidos >= 1  and  dias_vencidos <= 30 then pendiente else Cast( 0.0 as decimal(12,2)) end  ) as decimal(12,2)) as 1_30_dias ,
                    CAST(Sum( case when dias_vencidos >= 31  and  dias_vencidos <= 60 then pendiente else Cast( 0.0 as decimal(12,2)) end  ) as decimal(12,2)) as 31_60_dias ,
                    CAST(Sum( case when dias_vencidos >= 61  and  dias_vencidos <= 90 then pendiente else Cast( 0.0 as decimal(12,2)) end  ) as decimal(12,2)) as 61_90_dias ,
                    CAST(Sum( case when dias_vencidos >= 91  then pendiente else 0 end  ) as decimal(12,2))  as mas_90_dias,
                    Sum(  pendiente  ) as total
                    FROM (
        
                    select aa.id_vencimiento, aa.id_documento,aa.factura, aa.proveedor, aa.identificacion, aa.secuencial, aa.fecha,  aa.condicion_pago, aa.tipo_vencimiento,
                     aa.valor_vencimiento, aa.fecha_vencimiento, CASE WHEN  aa.dias_vencido IS NULL OR aa.dias_vencido <0 THEN 0 ELSE aa.dias_vencido END AS dias_vencidos  ,
                     CASE WHEN bb.pagado IS NULL THEN cast(0.0 as decimal(12,2)) ELSE bb.pagado end as pagado,
                     aa.valor_vencimiento - case when bb.pagado IS NULL THEN cast(0.0 as decimal(12,8)) else bb.pagado end as pendiente
                    from (
                    select a.id as id_vencimiento, a.purchase_id as id_documento,b.sequential_number factura, b.date_of_issue AS fecha,
                     p.name as proveedor, p.number as identificacion, b.sequential_number AS secuencial ,
                      b.payment_condition_id, c.name as condicion_pago ,
                      case when  b.payment_condition_id = '03' THEN 'Credito' else   d.description end as tipo_vencimiento, a.amount as valor_vencimiento,
                      a.date fecha_vencimiento, DATEDIFF (cast(fechacorte as date ), a.date ) as dias_vencido
                      from purchase_fee  as a
                      inner join purchases as b on a.purchase_id = b.id
                      left join (select id, name from payment_conditions  group by id, name ) as c on  b.payment_condition_id = c.id
                      left join (select id, description from payment_method_types GROUP BY id, description ) as d on a.payment_method_type_id = d.id
                      left join persons as p on b.supplier_id = p.id
                      where b.payment_condition_id IN ('02','03') AND
                      (b.supplier_id = codproveedor OR 0 = codproveedor  )
        
                    ) as aa left join
                    ( select   a.purchase_id, a.fee_id ,   sum(a.payment) as pagado
                     from purchase_payments  as a inner join purchases as b on a.purchase_id = b.id
                     where b.payment_condition_id IN ('02','03')
                     and a.date_of_payment <= fechacorte
                     GROUP by a.purchase_id, a.fee_id ) as bb on aa.id_documento = bb.purchase_id and aa.id_vencimiento = bb.fee_id
                    ) as b
                    group By id_documento, proveedor, identificacion  ;
        
                     ELSEIF sum_null = 1 THEN
        
        
                    Select proveedor, identificacion 
                     ,
                     CAST(SUM( case when dias_vencidos = 0 then pendiente else 0 end  ) as decimal(12,2))  as por_vencer ,
                    Cast(Sum( case when dias_vencidos >= 1  and  dias_vencidos <= 30 then pendiente else Cast( 0.0 as decimal(12,2)) end  ) as decimal(12,2)) as 1_30_dias ,
                    CAST(Sum( case when dias_vencidos >= 1  and  dias_vencidos <= 30 then pendiente else Cast( 0.0 as decimal(12,2)) end  ) as decimal(12,2)) as 1_30_dias ,
                    CAST(Sum( case when dias_vencidos >= 31  and  dias_vencidos <= 60 then pendiente else Cast( 0.0 as decimal(12,2)) end  ) as decimal(12,2)) as 31_60_dias ,
                    CAST(Sum( case when dias_vencidos >= 61  and  dias_vencidos <= 90 then pendiente else Cast( 0.0 as decimal(12,2)) end  ) as decimal(12,2)) as 61_90_dias ,
                    CAST(Sum( case when dias_vencidos >= 91  then pendiente else 0 end  ) as decimal(12,2))  as mas_90_dias,
                    Sum(  pendiente  ) as total
                    FROM (
        
                    select aa.id_vencimiento, aa.id_documento,aa.factura, aa.proveedor, aa.identificacion,  aa.condicion_pago, aa.tipo_vencimiento,
                     aa.valor_vencimiento, aa.fecha_vencimiento, CASE WHEN  aa.dias_vencido IS NULL OR aa.dias_vencido <0 THEN 0 ELSE aa.dias_vencido END AS dias_vencidos  ,
                     CASE WHEN bb.pagado IS NULL THEN cast(0.0 as decimal(12,2)) ELSE bb.pagado end as pagado,
                     aa.valor_vencimiento - case when bb.pagado IS NULL THEN cast(0.0 as decimal(12,8)) else bb.pagado end as pendiente
                    from (
                    select a.id as id_vencimiento, a.purchase_id as id_documento,b.sequential_number factura,
                     p.name as proveedor, p.number as identificacion,
                      b.payment_condition_id, c.name as condicion_pago ,
                      case when  b.payment_condition_id = '03' THEN 'Credito' else   d.description end as tipo_vencimiento, a.amount as valor_vencimiento,
                      a.date fecha_vencimiento, DATEDIFF (cast(fechacorte as date ), a.date ) as dias_vencido
                      from purchase_fee  as a
                      inner join purchases as b on a.purchase_id = b.id
                      left join (select id, name from payment_conditions  group by id, name ) as c on  b.payment_condition_id = c.id
                      left join (select id, description from payment_method_types GROUP BY id, description ) as d on a.payment_method_type_id = d.id
                      left join persons as p on b.supplier_id = p.id
                      where b.payment_condition_id IN ('02','03') AND
                      (b.supplier_id = codproveedor OR 0 = codproveedor  )
        
                    ) as aa left join
                    ( select   a.purchase_id, a.fee_id ,   sum(a.payment) as pagado
                     from purchase_payments  as a inner join purchases as b on a.purchase_id = b.id
                     where b.payment_condition_id IN ('02','03')
                     and a.date_of_payment <= fechacorte
                     GROUP by a.purchase_id, a.fee_id ) as bb on aa.id_documento = bb.purchase_id and aa.id_vencimiento = bb.fee_id
                    ) as b
                    group By proveedor, identificacion 
                  
                     
                    ;
                    END IF;
        
        
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
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_toPay_statement";
        DB::connection('tenant')->statement($sqlDelete);
    }
}

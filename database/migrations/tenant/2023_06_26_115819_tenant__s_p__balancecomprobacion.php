<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantSPBalancecomprobacion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql_create = "
        CREATE PROCEDURE SP_Balancecomprobacion(IN date_start DATE, IN date_end DATE, IN icuenta INT, IN fcuenta INT)
        BEGIN
        SET @d = date_start ;
        SET @h = date_end ;
        SET @icuenta = icuenta ;
        SET @fcuenta = fcuenta ;

        SELECT AA.Cuenta, AA.Descripcion_cuenta, AA.Saldo_inicial,
        AA.Debe, AA.Haber,
        Case when diferencia >0 then abs(diferencia) ELSE 0 END as Saldo_deudor,
        Case when diferencia <0 then abs(diferencia) ELSE 0 END as Saldo_acreedor,
        (AA.Saldo_inicial - AA.diferencia ) as Saldo_final
        FROM (
        SELECT b.code as Cuenta ,
        b.description AS  Descripcion_cuenta ,
        IFNULL(( SELECT  SUM(a1.debe) - SUM(a1.haber) diferencia
        FROM  accounting_entry_items AS a1 LEFT JOIN account_movements AS b1
        ON a1.account_movement_id = b1.id
        left join accounting_entries AS c1 ON a1.accounting_entrie_id = c1.seat
        WHERE b1.code  = b.code AND c1.seat_date < @d ),0) Saldo_inicial,
         sum(a.debe) AS Debe, sum(a.haber) AS Haber,
         sum(a.debe) - sum(a.haber) diferencia
         FROM  accounting_entry_items AS a LEFT JOIN account_movements AS b
        ON a.account_movement_id = b.id
        left join accounting_entries AS c ON a.accounting_entrie_id = c.seat
        WHERE c.seat_date >= @d
        AND c.seat_date <= @h
        AND b.code >= @icuenta
        AND b.code <= @fcuenta
        GROUP BY  b.code , b.description
        ) AS AA

        END
        ";
        DB::connection('tenant')->statement($sql_create);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql_delete = "
            DROP PROCEDURE IF EXISTS SP_Balancecomprobacion;
        ";
        DB::connection('tenant')->statement($sql_delete);
    }
}

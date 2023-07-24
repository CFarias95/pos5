<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantSPBalanceresultados extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql_create = "
        CREATE PROCEDURE SP_Balanceresultados(IN d INT, IN date_start DATE, IN date_end DATE)
        BEGIN
            SET @d = d ; 
            SET @desde = date_start ;
            SET @hasta = date_end ;
            
            SELECT P,CODE, DESCRIPTION, valor
            FROM (
            SELECT P, aa.CODE, DESCRIPTION, valor FROM (
            SELECT  1 as P ,SUBSTRING(b.code,1,1) code ,
            sum(debe- haber ) Valor
            FROM accounting_entry_items AS a INNER JOIN account_movements AS b ON a.account_movement_id = b.id
            INNER JOIN accounting_entries AS d ON a.accounting_entrie_id = d.id
            INNER JOIN account_groups AS g ON b.account_group_id = g.id
            WHERE d.seat_date >= @desde AND d.seat_date <=@hasta AND g.`type` = 'RESULTADOS'
            GROUP BY SUBSTRING(b.CODE,1,1) 
            union all
            SELECT  3 as P ,SUBSTRING(b.code,1,3) code ,
            sum(debe- haber ) Valor
            FROM accounting_entry_items AS a INNER JOIN account_movements AS b ON a.account_movement_id = b.id
            INNER JOIN accounting_entries AS d ON a.accounting_entrie_id = d.id
            INNER JOIN account_groups AS g ON b.account_group_id = g.id
            WHERE d.seat_date >= @desde AND d.seat_date <=@hasta AND g.`type` = 'RESULTADOS'
            GROUP BY SUBSTRING(b.CODE,1,3) 
            UNION ALL
            SELECT  5 as P ,SUBSTRING(b.CODE,1,5) code ,
            sum(debe- haber ) Valor
            FROM accounting_entry_items AS a INNER JOIN account_movements AS b ON a.account_movement_id = b.id
            INNER JOIN accounting_entries AS d ON a.accounting_entrie_id = d.id
            INNER JOIN account_groups AS g ON b.account_group_id = g.id
            WHERE d.seat_date >= @desde AND d.seat_date <=@hasta AND g.`type` = 'RESULTADOS'
            GROUP BY SUBSTRING(b.CODE,1,5) 
            UNION ALL
            SELECT  7 as P ,SUBSTRING(b.CODE,1,7) code ,
            sum(debe- haber ) Valor
            FROM accounting_entry_items AS a INNER JOIN account_movements AS b ON a.account_movement_id = b.id
            INNER JOIN accounting_entries AS d ON a.accounting_entrie_id = d.id
            INNER JOIN account_groups AS g ON b.account_group_id = g.id
            WHERE d.seat_date >= @desde AND d.seat_date <=@hasta AND g.`type` = 'RESULTADOS'
            GROUP BY SUBSTRING(b.CODE,1,7) 
            
            ) AS aa
            INNER JOIN account_groups AS bb ON aa.code = bb.code
            union ALL
            SELECT 0 P  ,b.code , b.description , 
            sum(debe- haber ) Valor
            FROM accounting_entry_items AS a INNER JOIN account_movements AS b ON a.account_movement_id = b.id
            INNER JOIN accounting_entries AS d ON a.accounting_entrie_id = d.id
            INNER JOIN account_groups AS g ON b.account_group_id = g.id
            WHERE d.seat_date >= @desde AND d.seat_date <=@hasta AND g.`type` = 'RESULTADOS'
            and 1 = @d
            GROUP BY b.code , b.description
            ) AS aaa
            ORDER BY aaa.code ;
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
            DROP PROCEDURE SP_Balanceresultados;
        ";
        DB::connection('tenant')->statement($sql_delete);
    }
}
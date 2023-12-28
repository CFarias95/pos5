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
        $sql_create = <<< EOF
        CREATE PROCEDURE `SP_Balanceresultados`(
            IN `d` INT,
            IN `date_start` DATE,
            IN `date_end` DATE,
            IN `pormeses` INT
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN
                
                DECLARE balgeneral DECIMAL(10,2) DEFAULT 0;
                SELECT pormeses INTO balgeneral ; 
                 IF balgeneral = 0 THEN
                
                SELECT P,CODE, DESCRIPTION, valor
                FROM (
                SELECT P, aa.CODE, DESCRIPTION, valor FROM (
                SELECT  1 as P ,SUBSTRING(b.CODE,1,1) code ,
                sum(debe- haber ) Valor
                FROM accounting_entry_items AS a INNER JOIN account_movements AS b ON a.account_movement_id = b.id
                INNER JOIN accounting_entries AS d ON a.accounting_entrie_id = d.id
                INNER JOIN account_groups AS g ON b.account_group_id = g.id
                WHERE d.seat_date >= date_start AND d.seat_date <=date_end AND g.`type` <> 'RESULTADOS'
                GROUP BY SUBSTRING(CODE,1,1) 
                union all
                SELECT  3 as P ,SUBSTRING(b.CODE,1,3) code ,
                sum(debe- haber ) Valor
                FROM accounting_entry_items AS a INNER JOIN account_movements AS b ON a.account_movement_id = b.id
                INNER JOIN accounting_entries AS d ON a.accounting_entrie_id = d.id
                INNER JOIN account_groups AS g ON b.account_group_id = g.id
                WHERE d.seat_date >= date_start AND d.seat_date <=date_end AND g.`type` <> 'RESULTADOS'
                GROUP BY SUBSTRING(CODE,1,3) 
                UNION ALL
                SELECT  5 as P ,SUBSTRING(b.CODE,1,5) code ,
                sum(debe- haber ) Valor
                FROM accounting_entry_items AS a INNER JOIN account_movements AS b ON a.account_movement_id = b.id
                INNER JOIN accounting_entries AS d ON a.accounting_entrie_id = d.id
                INNER JOIN account_groups AS g ON b.account_group_id = g.id
                WHERE d.seat_date >= date_start AND d.seat_date <=date_end AND g.`type` <> 'RESULTADOS'
                GROUP BY SUBSTRING(CODE,1,5) 
                UNION ALL
                SELECT  7 as P ,SUBSTRING(b.CODE,1,7) code ,
                sum(debe- haber ) Valor
                FROM accounting_entry_items AS a INNER JOIN account_movements AS b ON a.account_movement_id = b.id
                INNER JOIN accounting_entries AS d ON a.accounting_entrie_id = d.id
                  INNER JOIN account_groups AS g ON b.account_group_id = g.id
                WHERE d.seat_date >= date_start AND d.seat_date <=date_end AND g.`type` <> 'RESULTADOS'
                GROUP BY SUBSTRING(CODE,1,7) 
                
                ) AS aa
                INNER JOIN account_groups AS bb ON aa.code = bb.code
                union ALL
                SELECT 0 P  ,b.code , b.description , 
                sum(debe- haber ) Valor
                FROM accounting_entry_items AS a INNER JOIN account_movements AS b ON a.account_movement_id = b.id
                INNER JOIN accounting_entries AS d ON a.accounting_entrie_id = d.id
                INNER JOIN account_groups AS g ON b.account_group_id = g.id
                WHERE d.seat_date >= date_start AND d.seat_date <=date_end AND g.`type` <> 'RESULTADOS'
                and 1 = d
                GROUP BY b.code , b.description
                ) AS aaa
                ORDER BY aaa.code ;
                
                ELSEIF balgeneral = 1 THEN
                
                        DROP TABLE IF EXISTS TMP_BG1;
                CREATE TEMPORARY TABLE TMP_BG1
                AS (
                SELECT P,CODE, nm,
                  CONCAT(UCASE(LEFT(mes, 1)),  LCASE(SUBSTRING(mes, 2))) AS mes, 
                  DESCRIPTION, valor
                FROM (
                SELECT P, aa.CODE,nm,mes, DESCRIPTION, valor FROM (
                SELECT  1 as P ,SUBSTRING(b.CODE,1,1) CODE ,   MONTH(d.seat_date) nm , MONTHNAME(d.seat_date) mes ,
                sum(debe- haber ) Valor
                FROM accounting_entry_items AS a INNER JOIN account_movements AS b ON a.account_movement_id = b.id
                INNER JOIN accounting_entries AS d ON a.accounting_entrie_id = d.id
                INNER JOIN account_groups AS g ON b.account_group_id = g.id
                WHERE d.seat_date >= date_start AND d.seat_date <=date_end AND g.`type` <> 'RESULTADOS'
                GROUP BY SUBSTRING(CODE,1,1) , MONTH(d.seat_date), MONTHNAME(d.seat_date)
                union all
                SELECT  3 as P ,SUBSTRING(b.CODE,1,3) code ,  MONTH(d.seat_date) nm , MONTHNAME(d.seat_date) mes ,
                sum(debe- haber ) Valor
                FROM accounting_entry_items AS a INNER JOIN account_movements AS b ON a.account_movement_id = b.id
                INNER JOIN accounting_entries AS d ON a.accounting_entrie_id = d.id
                INNER JOIN account_groups AS g ON b.account_group_id = g.id
                WHERE d.seat_date >= date_start AND d.seat_date <=date_end AND g.`type` <> 'RESULTADOS'
                GROUP BY SUBSTRING(CODE,1,3) ,  MONTH(d.seat_date), MONTHNAME(d.seat_date)
                UNION ALL
                SELECT  5 as P ,SUBSTRING(b.CODE,1,5) CODE , MONTH(d.seat_date) nm ,  MONTHNAME(d.seat_date) mes ,
                sum(debe- haber ) Valor
                FROM accounting_entry_items AS a INNER JOIN account_movements AS b ON a.account_movement_id = b.id
                INNER JOIN accounting_entries AS d ON a.accounting_entrie_id = d.id
                INNER JOIN account_groups AS g ON b.account_group_id = g.id
                WHERE d.seat_date >= date_start AND d.seat_date <=date_end AND g.`type` <> 'RESULTADOS'
                GROUP BY SUBSTRING(CODE,1,5) ,  MONTH(d.seat_date), MONTHNAME(d.seat_date)
                UNION ALL
                SELECT  7 as P ,SUBSTRING(b.CODE,1,7) CODE , MONTH(d.seat_date) nm ,  MONTHNAME(d.seat_date) mes ,
                sum(debe- haber ) Valor
                FROM accounting_entry_items AS a INNER JOIN account_movements AS b ON a.account_movement_id = b.id
                INNER JOIN accounting_entries AS d ON a.accounting_entrie_id = d.id
                  INNER JOIN account_groups AS g ON b.account_group_id = g.id
                WHERE d.seat_date >= date_start AND d.seat_date <=date_end AND g.`type` <> 'RESULTADOS'
                GROUP BY SUBSTRING(CODE,1,7) ,  MONTH(d.seat_date)  , MONTHNAME(d.seat_date)
                
                ) AS aa
                INNER JOIN account_groups AS bb ON aa.code = bb.code 
                union ALL
                SELECT 0 P  ,b.code ,  MONTH(d.seat_date) nm , MONTHNAME(d.seat_date) mes , b.description , 
                sum(debe- haber ) Valor
                FROM accounting_entry_items AS a INNER JOIN account_movements AS b ON a.account_movement_id = b.id
                INNER JOIN accounting_entries AS d ON a.accounting_entrie_id = d.id
                INNER JOIN account_groups AS g ON b.account_group_id = g.id
                WHERE d.seat_date >= date_start AND d.seat_date <=date_end AND g.`type` <> 'RESULTADOS'
                and 1 = d
                GROUP BY b.code , b.description, MONTH(d.seat_date) ,  MONTHNAME(d.seat_date)
                ) AS aaa
                ORDER BY aaa.nm asc ) ;
                
        
                SET @SQLBG  = NULL;
                SET @SQLBG2  = NULL;
                SET @SQLBG3  = NULL;
        
         SELECT
                      (  
                           GROUP_CONCAT(
                             -- DISTINCT    
                                      CONCAT(
                                ' SUM(CASE WHEN a.nm = ',a.nm,' THEN (a.valor) ELSE 0 END) AS "', a.mes, '"')
                               )  
                                      ) AS dd
                            INTO @SQLBG
                            FROM (  SELECT nm, mes, SUM(valor) valor FROM TMP_BG1 GROUP BY nm , mes) AS a ;
                            
         SET @SQLBG2 =  CONCAT('SELECT a.P, a.CODE, a.DESCRIPTION, ' , @SQLBG  , ' ,SUM(a.valor) as Valor  FROM TMP_BG1  AS a
                                          GROUP BY a.P, a.CODE, a.DESCRIPTION ORDER BY a.CODE ' ) ;
                            
                        --	SELECT 			  @SQLBG2  AS QUERY ;
                                PREPARE bg FROM @SQLBG2;
                          EXECUTE bg;
                          DEALLOCATE PREPARE bg;
                
                END IF;
                END
        EOF;
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

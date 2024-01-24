<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantSPCuentarPorPagar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_CuentarPorPagar";
        DB::connection('tenant')->statement($sqlDelete);

        $sqlCREATE = "CREATE PROCEDURE `SP_CuentarPorPagar`(
            IN `establecimiento` INT,
            IN `supplier` JSON,
            IN `usuario` INT,
            IN `valor` INT,
            IN `liquidated` INT,
            IN `tipo` INT,
            IN `date_start` VARCHAR(50),
            IN `date_end` VARCHAR(50)
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN

        SELECT * FROM
            (
            SELECT d.id , df.id as fee_id,
            CASE WHEN df.id IS NOT NULL THEN DATE_FORMAT(d.date_of_issue, '%Y/%m/%d') ELSE DATE_FORMAT(d.date_of_issue, '%Y/%m/%d') END as date_of_issue,
            persons.name as supplier_name, persons.id as supplier_id
            ,d.document_type_id,
            CONCAT(d.series,'-',d.number,'/',d.sequential_number) AS number_full,
            df.number AS num_couta,
            CASE WHEN df.id IS NOT NULL THEN df.amount ELSE d.total END as total,
            CASE WHEN df.id IS NOT NULL THEN IFNULL(SUM(dp.payment),0) ELSE IFNULL(SUM(dp.payment), 0) END as total_payment,
            CASE WHEN df.id IS NOT NULL THEN df.amount - IFNULL(SUM(dp.payment),0) ELSE d.total - IFNULL(SUM(dp.payment), 0)  END as total_to_pay,
            CASE WHEN df.id IS NOT NULL THEN DATE_FORMAT(df.date, '%Y/%m/%d') ELSE  DATE_FORMAT(d.date_of_due , '%Y/%m/%d') END AS date_payment_last,
            CASE WHEN df.id IS NOT NULL THEN DATEDIFF(df.date, NOW()) ELSE DATEDIFF(d.date_of_due,NOW()) END AS delay_payment,
            CASE WHEN df.id IS NOT NULL THEN DATE_FORMAT(df.date, '%Y/%m/%d') ELSE  DATE_FORMAT(d.date_of_due , '%Y/%m/%d') END AS date_of_due,
            'purchase' AS 'type',d.currency_type_id,d.exchange_rate_sale, d.user_id, users.name as username,
            CASE WHEN df.id IS NOT NULL THEN df.amount - IFNULL(SUM(dp.payment),0) ELSE d.total - IFNULL(SUM(dp.payment), 0) END as total_subtraction,
            DATE_FORMAT(df.f_posdated, '%Y/%m/%d') f_posdated,df.posdated as posdated
            FROM purchases AS d
            JOIN purchase_fee AS df ON df.purchase_id = d.id
            LEFT JOIN purchase_payments AS dp ON dp.fee_id = df.id AND dp.purchase_id = d.id
            JOIN persons ON persons.id = d.supplier_id
            JOIN users ON users.id = d.user_id
            WHERE (d.establishment_id = establecimiento OR 0=establecimiento)
            AND (supplier LIKE CONCAT('%',d.supplier_id,'%') OR supplier LIKE '%[0]%')
            AND (d.user_id = usuario OR 0=usuario)
            GROUP BY id, df.id
        ) AS AA
            WHERE (AA.total >= valor OR 0=valor)
            AND CASE WHEN liquidated < 1 THEN AA.total_to_pay > 0 ELSE 0=0 END
            AND CASE WHEN tipo = 1 THEN AA.date_of_issue BETWEEN date_start AND date_end ELSE
            CASE WHEN tipo = 2 THEN AA.date_of_due BETWEEN date_start AND date_end ELSE
            CASE WHEN tipo = 3 THEN AA.f_posdated BETWEEN date_start AND date_end ELSE 0=0 END END END
            ORDER BY date_of_issue DESC;

        END";
        DB::connection('tenant')->statement($sqlCREATE);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_CuentarPorPagar";
        DB::connection('tenant')->statement($sqlDelete);
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantCreateSpCuentasporCobrarCarlos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_CuentarPorCobrar";
        $sqlCreate = "CREATE PROCEDURE `SP_CuentarPorCobrar`(
            IN `establecimiento` INT,
            IN `customer` INT,
            IN `usuario` INT,
            IN `purchaseorder` INT,
            IN `valor` INT,
            IN `liquidated` INT,
            IN `date_start` VARCHAR(50),
            IN `date_end` VARCHAR(50),
            IN `tipo` INT
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
            persons.name as customer_name, persons.id as customer_id
            ,d.document_type_id,
            CASE WHEN df.id IS NOT NULL THEN CONCAT(d.series,'-',d.number,'/',( SELECT NP FROM (SELECT row_number() OVER (ORDER BY id) AS NP,id FROM document_fee as dp WHERE dp.document_id =  d.id) AS FD WHERE FD.id = df.id)) ELSE CONCAT(d.series,'-',d.number) END AS number_full,
            CASE WHEN df.id IS NOT NULL THEN df.amount ELSE d.total END as total,
            CASE WHEN df.id IS NOT NULL THEN IFNULL(SUM(dpf.payment),0) ELSE IFNULL(SUM(dp.payment), 0) END as total_payment,
            IFNULL(SUM(dcn.total), 0) as total_credit_notes,
            CASE WHEN df.id IS NOT NULL THEN df.amount - IFNULL(SUM(dpf.payment),0) ELSE d.total - IFNULL(SUM(dp.payment), 0)  END as total_to_pay,
            CASE WHEN df.id IS NOT NULL THEN DATE_FORMAT(df.date, '%Y/%m/%d') ELSE  DATE_FORMAT(inv.date_of_due , '%Y/%m/%d') END AS date_payment_last,
            CASE WHEN df.id IS NOT NULL THEN DATEDIFF(df.date, NOW()) ELSE DATEDIFF(inv.date_of_due,NOW()) END AS delay_payment,
            CASE WHEN df.id IS NOT NULL THEN DATE_FORMAT(df.date, '%Y/%m/%d') ELSE  DATE_FORMAT(inv.date_of_due , '%Y/%m/%d') END AS date_of_due,
            'document' AS 'type',d.currency_type_id,d.exchange_rate_sale, d.user_id, users.name as username,
            CASE WHEN df.id IS NOT NULL THEN df.amount - IFNULL(SUM(dpf.payment),0) ELSE d.total - IFNULL(SUM(dp.payment), 0) END as total_subtraction,
            d.purchase_order AS purchase_order,
            DATE_FORMAT(df.f_posdated, '%Y/%m/%d') f_posdated,df.posdated as posdated
            FROM documents AS d
            LEFT JOIN document_payments AS dp ON dp.document_id = d.id
            LEFT JOIN document_fee AS df ON df.document_id = d.id
            JOIN persons ON persons.id = d.customer_id
            JOIN users ON users.id = d.user_id
            LEFT JOIN notes AS cn ON  cn.affected_document_id = d.id
            LEFT JOIN documents AS dcn ON dcn.id = cn.document_id
            LEFT JOIN document_payments AS dpf ON dpf.fee_id = df.id
            JOIN invoices AS inv ON inv.document_id = d.id
            WHERE (d.establishment_id = establecimiento OR 0=establecimiento)
            AND (d.customer_id = customer OR 0 = customer)
            AND (d.user_id = usuario OR 0=usuario)
            AND (d.purchase_order LIKE CONCAT('%',purchaseorder,'%') OR 0= purchaseorder)
            GROUP BY id, fee_id, inv.id
        ) AS AA
            WHERE (AA.total >= valor OR 0=valor)
            AND CASE WHEN liquidated < 1 THEN AA.total_to_pay > 0 ELSE 0=0 END
            AND CASE WHEN tipo = 1 THEN AA.date_of_issue BETWEEN date_start AND date_end ELSE
            CASE WHEN tipo = 2 THEN AA.date_of_due BETWEEN date_start AND date_end ELSE
            CASE WHEN tipo = 3 THEN AA.f_posdated BETWEEN date_start AND date_end ELSE 0=0 END END END
            ORDER BY date_of_issue DESC;
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
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_CuentarPorCobrar";
        DB::connection('tenant')->statement($sqlDelete);
    }
}

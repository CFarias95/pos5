<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class Tenantplancuenta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql_create = "
            CREATE PROCEDURE SP_PlanCuentas()
            BEGIN
                SELECT * FROM (
                    SELECT am.code AS 'codigo', am.description AS 'nombre', SUM(aei.debe+aei.haber) AS 'saldo' FROM accounting_entry_items aei
                    INNER JOIN account_movements am ON aei.account_movement_id = am.id
                    GROUP BY am.description, am.code
                    UNION ALL
                    SELECT ag.code AS 'codigo', ag.description AS 'nombre', SUM(aei.debe + aei.haber) AS 'saldo' FROM accounting_entries ae
                    INNER JOIN accounting_entry_items aei ON ae.id = aei.accounting_entrie_id
                    INNER JOIN account_movements am ON aei.account_movement_id = am.id
                    INNER JOIN account_groups ag ON am.account_group_id = ag.id
                    GROUP BY ag.description, ag.code
                ) AS A 
                ORDER BY A.codigo;
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
            DROP PROCEDURE SP_PlanCuentasCategoria;
        ";
        DB::connection('tenant')->statement($sql_delete);
    }
}

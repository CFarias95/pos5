<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class Tenantcreatesp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Para crear, altera, inserta
        //Nombre Cliente total Facturado
        $sql = "
        

        CREATE PROCEDURE SP_VentasPorCliente(IN cliente_nombre VARCHAR(35))
        BEGIN
            SELECT d.customer_id AS 'ID Cliente', p.name AS 'Nombre Cliente', SUM(d.total) AS 'Total Venta' FROM persons p
            INNER JOIN documents d 
            ON p.id = d.customer_id
            WHERE p.name = cliente_nombre
            GROUP BY d.customer_id;
        END
        
        
        ";
        DB::connection('tenant')->statement($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //En caso de error
        $sql_delete = "
            DROP PROCEDURE SP_VentasPorCliente;
        ";
        DB::connection('tenant')->statement($sql_delete);
    }
}

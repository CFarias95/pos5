<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantSpReporteClientesProveedoresAnticipos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_Reporte_Anticipo_ClienteProveedor";
        $sqlCreate = "CREATE PROCEDURE `SP_Reporte_Anticipo_ClienteProveedor`(
            IN `fini` DATE,
            IN `ffin` DATE
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN
        SELECT a.id, 'Clientes' AS Tipo, CAST(a.updated_at AS DATE) AS Fecha, a.reference AS referencia, a.idcliente, p.name AS Nombre, p.number AS RUC,
        a.valor, CASE WHEN in_use  = 0 THEN 'No usado' ELSE 'Usado' END AS Estado
        FROM advances  AS a LEFT JOIN persons AS p ON a.idCliente = p.id
        WHERE is_supplier = 0  
        AND CAST(a.updated_at AS DATE)>= fini
        AND CAST(a.updated_at AS DATE)<= ffin
        UNION ALL
        SELECT a.id, 'Proveedores' AS Tipo, CAST(a.updated_at AS DATE) AS Fecha, a.reference AS referencia, a.idcliente, p.name AS Nombre, p.number AS RUC,
        a.valor, CASE WHEN in_use  = 0 THEN 'No usado' ELSE 'Usado' END AS Estado
        FROM advances  AS a LEFT JOIN persons AS p ON a.idCliente = p.id
        WHERE is_supplier = 1  
        AND CAST(a.updated_at AS DATE)>= fini
        AND CAST(a.updated_at AS DATE)<= ffin
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
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_Reporte_Anticipo_ClienteProveedor";
        DB::connection('tenant')->statement($sqlDelete);
    }
}

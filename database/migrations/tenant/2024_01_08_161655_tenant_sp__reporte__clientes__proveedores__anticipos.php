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
        $sqlCreate = "CREATE DEFINER=`tenancy_veterinaria`@`127.0.0.1` PROCEDURE `SP_Reporte_Anticipo_ClienteProveedor`(
	IN `fini` DATE,
	IN `ffin` DATE,
	IN `person` INT,
	IN `type_id` INT,
	IN `status_id` INT
)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN
	SELECT * FROM (
        SELECT a.id, 'Clientes' AS Tipo, CAST(a.created_at AS DATE) AS Fecha, a.reference AS referencia, a.idcliente, p.name AS Nombre, p.number AS RUC,
        a.valor, CASE WHEN in_use  = 0 THEN 'No usado' ELSE 'Usado' END AS Estado, a.observation AS Observaciones
        FROM advances  AS a
		  LEFT JOIN persons AS p ON a.idCliente = p.id
        WHERE is_supplier = 0
        AND CAST(a.created_at AS DATE)>= fini
        AND CAST(a.created_at AS DATE)<= ffin
        AND (p.id = person OR 0 = person)
        AND (a.is_supplier = type_id OR type_id = 2 )
        AND (a.in_use = status_id OR 2 = status_id )

        UNION ALL

        SELECT a.id, 'Proveedores' AS Tipo, CAST(a.created_at AS DATE) AS Fecha, a.reference AS referencia, a.idcliente, p.name AS Nombre, p.number AS RUC,
        a.valor, CASE WHEN in_use  = 0 THEN 'No usado' ELSE 'Usado' END AS Estado, a.observation AS Observaciones
        FROM advances  AS a LEFT JOIN persons AS p ON a.idCliente = p.id
        WHERE is_supplier = 1
        AND CAST(a.created_at AS DATE)>= fini
        AND CAST(a.created_at AS DATE)<= ffin
        AND (p.id = person OR 0 = person)
        AND (a.is_supplier = type_id OR type_id = 2 )
        AND (a.in_use = status_id OR 2 = status_id )
      ) AS A
		ORDER BY A.Fecha DESC;
   END
        ";

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

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantReporteStockAlmacen extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql_create = "
            CREATE PROCEDURE SP_StockAlmacen()
            BEGIN
            SET @sql = NULL;
            SET @SQL2 = NULL;
            SELECT
            GROUP_CONCAT(DISTINCT CONCAT(
                ' SUM(
                  CASE WHEN a.warehouse_id = ', a.warehouse_id , ' THEN a.stock ELSE 0 END) 
                  AS ', CONCAT(CHAR(39), CAST( REPLACE(REPLACE(b.description,' ' ,''),'-','') AS CHAR(100)) , CHAR(39) ) ,''  )
              )
            INTO @sql
            FROM item_warehouse AS a INNER JOIN warehouses AS b ON a.warehouse_id = b.id    ;
            Select
            GROUP_CONCAT(DISTINCT CONCAT(
              ', sum(a.stock) AS Stocktotal ' )
              )
            INTO @SQL2
            FROM item_warehouse AS a ;
            
            
            SET @sql = CONCAT('  
            SELECT a.item_id, b.internal_id as Codigointerno,  b.name as Nombreproducto , 
            c.name Categoria, d.name Marca , b.unit_type_id Unidadmedida,  ', @SQL, @SQL2 , 
              '
              FROM item_warehouse as a inner join items as b on a.item_id = b.id 
              left join categories as c on b.category_id = c.id 
              left join brands as d on b.brand_id = d.id
              GROUP BY a.item_id, b.name 
            
            
              ');  
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
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
            DROP PROCEDURE SP_StockAlmacen();
        ";
        DB::connection('tenant')->statement($sql_delete);
    }
}

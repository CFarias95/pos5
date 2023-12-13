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
        $sql_delete = "DROP PROCEDURE IF EXISTS SP_StockAlmacen";
        DB::connection('tenant')->statement($sql_delete);

        $sql_create = `CREATE PROCEDURE 'SP_StockAlmacen'(
            IN 'warehouse_id' INT,
            IN 'item_id' INT,
            IN 'c' INT,
            IN 'm' INT,
            IN 'l' VARCHAR(50)
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
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
                            FROM item_warehouse AS a INNER JOIN warehouses AS b ON a.warehouse_id = b.id
                            AND ( 0 = warehouse_id OR a.warehouse_id = warehouse_id)
                                AND ( 0 = item_id OR a.item_id = item_id);
                            Select
                            GROUP_CONCAT(DISTINCT CONCAT(
                              CASE WHEN warehouse_id = 0 AND item_id = 0 THEN
                              "'', sum(a.stock) AS Stocktotal "
                              ELSE  ', sum(a.stock) AS Stocktotal ' END
                                     )
                              )
                            INTO @SQL2
                            FROM item_warehouse AS a ;
        
        
                            SET @sql = CONCAT('
                            SELECT a.item_id, b.internal_id as Codigointerno,  b.name as Nombreproducto ,
                            dc.name AS departamento , cc.name AS seccion , sc.name as categoria, fc.name as familia, d.name Marca , b.line AS Linea , b.unit_type_id Unidadmedida, CASE WHEN b.purchase_mean_cost IS NULL THEN 0 ELSE
                                   CAST( b.purchase_mean_cost as decimal(16,3)) END AS Costo , 
                                  CASE WHEN b.sale_unit_price IS NULL THEN 0 ELSE cast( b.sale_unit_price as decimal(16,2)) END as Pvp, ', @SQL, @SQL2 , 
                              '
                              FROM item_warehouse as a inner join items as b on a.item_id = b.id
                              left join categories as c on b.category_id = c.id
                              left join brands as d on b.brand_id = d.id
                              left join
                                      (
                                                select id, category_id_array AS cat ,
                                                        @num := LENGTH(REPLACE(REPLACE(REPLACE(category_id_array,"[",""),"]","" ),", ","")) AS num,
                                                        IF(@num > 1,SUBSTRING_INDEX(REPLACE(REPLACE(category_id_array,"[",""),"]",""),",",1) , NULL) as coddepartamento,
                                                        IF(@num > 1,SUBSTRING_INDEX(SUBSTRING_INDEX(REPLACE(REPLACE(category_id_array,"[",""),"]",""),",",2),",",-1) , NULL) as codcategoria,
                                                        IF(@num > 1,SUBSTRING_INDEX(SUBSTRING_INDEX(REPLACE(REPLACE(category_id_array,"[",""),"]",""),",",-2),",",1) , NULL) as codseccion,
                                                        IF(@num > 1,SUBSTRING_INDEX(REPLACE(REPLACE(category_id_array,"[",""),"]",""),",",-1) , NULL) as codfamilia
                                                    from items as a
                                                    WHERE a.category_id_array IS NOT NULL        
                                              )
        
         AS aa on a.item_id = aa.id
         left join categories as  dc on aa.coddepartamento = dc.id
                        left join categories as  cc on aa.codcategoria = cc.id
                        left join categories as  sc on aa.codseccion = sc.id
                        left join categories as  fc on aa.codfamilia = fc.id
                              WHERE ( 0 = ',warehouse_id,' OR a.warehouse_id = ',warehouse_id,')
                                  AND ( 0 = ',item_id,' OR a.item_id = ',item_id,')
                                  AND 
        ( ( coddepartamento = ',c,' OR 0 =',c,' ) OR
        (codcategoria = ',c,' OR  0 =',c,' ) OR
        (codseccion =' ,c,' OR 0 = ',c,' ) OR
        (codfamilia =',c,' OR 0 = ',c,' ) 
        )
        AND ( brand_id =',m,' OR 0 = ',m,') 
        AND (  UPPER(b.line) = UPPER(''',l,''') OR UPPER('''') = UPPER(''',l,''')) 
                              GROUP BY a.item_id, b.name , dc.name , cc.name, sc.name, fc.name
        
                              ');
                         --     SELECT @SQL ;
                          PREPARE stmt FROM @sql;
                          EXECUTE stmt;
                          DEALLOCATE PREPARE stmt;
                            END`;
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
            DROP PROCEDURE SP_StockAlmacen;
        ";
        DB::connection('tenant')->statement($sql_delete);
    }
}

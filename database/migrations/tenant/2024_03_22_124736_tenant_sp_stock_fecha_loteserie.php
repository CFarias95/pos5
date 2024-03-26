<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantSpStockFechaLoteserie extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_Stock_Fecha_LoteSerie";
        DB::connection('tenant')->statement($sqlDelete);

        $sqlCREATE = <<< EOF
        CREATE PROCEDURE `SP_Stock_Fecha_LoteSerie`(
            IN `fecha` DATE
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN
            SET @fecha = fecha;
        
           DROP TABLE IF EXISTS TMP_STOCK_LOTE_V;
                CREATE TEMPORARY TABLE TMP_STOCK_LOTE_V
                AS (
         SELECT  warehouse_id ,item_id, lot_code
         FROM (
        
        SELECT  warehouse_id ,item_id , lot_code, SUM(cantidad) AS cantidad
        FROM (
         SELECT  b.warehouse_id , b.item_id,
         -- , b.document_id ,
        REPLACE(JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[0]'),'$.code'),'"','') as lot_code ,
        JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[0]'),'$.compromise_quantity') AS cantidad
          FROM document_items AS b INNER JOIN documents AS a ON b.document_id = a.id
        WHERE a.date_of_issue >= '2024-01-01'
        AND a.date_of_issue <= @fecha 
        -- AND item_id = 1247
        UNION all
        
         SELECT   b.warehouse_id , b.item_id,
         -- , b.document_id ,
        REPLACE(JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[1]'),'$.code'),'"','') as lot_code ,
        JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[1]'),'$.compromise_quantity') AS cantidad
          FROM document_items AS b INNER JOIN documents AS a ON b.document_id = a.id
        WHERE a.date_of_issue >= '2024-01-01'
        AND a.date_of_issue <= @fecha 
        -- AND item_id = 1247
        UNION all
        
         SELECT  b.warehouse_id ,  b.item_id,
         -- , b.document_id ,
        REPLACE(JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[2]'),'$.code'),'"','') as lot_code ,
        JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[2]'),'$.compromise_quantity') AS cantidad
          FROM document_items AS b INNER JOIN documents AS a ON b.document_id = a.id
        WHERE a.date_of_issue >= '2024-01-01'
        AND a.date_of_issue <= @fecha 
        -- AND item_id = 1247
        UNION all
        
        SELECT  b.warehouse_id , b.item_id,
        -- , b.document_id ,
        REPLACE(JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[3]'),'$.code'),'"','') as lot_code ,
        JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[3]'),'$.compromise_quantity') AS cantidad
          FROM document_items AS b INNER JOIN documents AS a ON b.document_id = a.id
        WHERE a.date_of_issue >= '2024-01-01'
        AND a.date_of_issue <= @fecha 
        -- AND item_id = 1247
        
        UNION all
         SELECT  b.warehouse_id , b.item_id,
         -- , b.document_id ,
        REPLACE(JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[4]'),'$.code'),'"','') as lot_code ,
        JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[4]'),'$.compromise_quantity') AS cantidad
          FROM document_items AS b INNER JOIN documents AS a ON b.document_id = a.id
        WHERE a.date_of_issue >= '2024-01-01'
        AND a.date_of_issue <= @fecha 
        -- AND item_id = 1247
        
        UNION all
         SELECT  b.warehouse_id , b.item_id,
         -- , b.document_id ,
        REPLACE(JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[5]'),'$.code'),'"','') as lot_code ,
        JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[5]'),'$.compromise_quantity') AS cantidad
          FROM document_items AS b INNER JOIN documents AS a ON b.document_id = a.id
        WHERE a.date_of_issue >= '2024-01-01'
        AND a.date_of_issue <= @fecha 
        -- AND item_id = 1247
        
        UNION all
         SELECT  b.warehouse_id , b.item_id,
         -- , b.document_id ,
        REPLACE(JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[5]'),'$.code'),'"','') as lot_code ,
        JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[5]'),'$.compromise_quantity') AS cantidad
          FROM document_items AS b INNER JOIN documents AS a ON b.document_id = a.id
        WHERE a.date_of_issue >= '2024-01-01'
        AND a.date_of_issue <= @fecha 
        -- AND item_id = 1247
        
        UNION all
         SELECT  b.warehouse_id , b.item_id,
         -- , b.document_id ,
        REPLACE(JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[6]'),'$.code'),'"','') as lot_code ,
        JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[6]'),'$.compromise_quantity') AS cantidad
          FROM document_items AS b INNER JOIN documents AS a ON b.document_id = a.id
        WHERE a.date_of_issue >= '2024-01-01'
        AND a.date_of_issue <= @fecha 
        -- AND item_id = 1247
        ) AS aa WHERE aa.lot_code IS NOT NULL 
        GROUP BY  warehouse_id , item_id , lot_code
         ) AS aa WHERE aa.lot_code IS NOT NULL 
         GROUP BY  warehouse_id , item_id, lot_code
        ) ;
         
        
        
        
        
                DROP TABLE IF EXISTS TMP_STOCK_LOTE;
                CREATE TEMPORARY TABLE TMP_STOCK_LOTE
                AS (
        SELECT warehouse_id, item_id, lot_code ,
        CAST(0.0 AS DECIMAL(16,8)) AS inicial ,
        CAST(0.0 AS DECIMAL(16,8)) AS compras ,
        CAST(0.0 AS DECIMAL(16,8)) AS tr_entradas ,
        CAST(0.0 AS DECIMAL(16,8)) AS tr_salidas ,
        CAST(0.0 AS DECIMAL(16,8)) AS entradas ,
        CAST(0.0 AS DECIMAL(16,8)) AS salidas ,
        CAST(0.0 AS DECIMAL(16,8)) AS ventas ,
        CAST(0.0 AS DECIMAL(16,8)) AS ventas_anul ,
        CAST(0.0 AS DECIMAL(16,8)) AS salidaproduccion ,
        CAST(0.0 AS DECIMAL(16,8)) AS entradaproduccion ,
        CAST(0.0 AS DECIMAL(16,8)) AS stockfinal 
        FROM (
        
        SELECT  a.warehouse_id,   item_id, a.lot_code 
        FROM inventories AS a INNER JOIN items AS i ON a.item_id = i.id
        WHERE inventory_transaction_id = 16
        AND  i.unit_type_id <> 'ZZ'
        AND a.lot_code IS NOT NULL
        group BY a.warehouse_id, a.item_id, a.lot_code
        UNION ALL 
        SELECT b.warehouse_id, b.item_id ,b.lot_code 
         FROM purchases AS a INNER join purchase_items AS b ON a.id = b.purchase_id
         INNER JOIN items AS i ON b.item_id = i.id
        WHERE b.lot_code IS NOT NULL 
        AND i.lots_enabled = 1
        -- AND b.item LIKE '%lots_enabled": true%'
        -- AND a.date_of_issue >='2023-01-01'
        GROUP BY b.warehouse_id , b.item_id ,b.lot_code
        UNION ALL
        SELECT  i.warehouse_id, i.item_id, i.lot_code
        FROM inventories AS i INNER JOIN inventory_transactions AS b ON i.inventory_transaction_id = b.id 
        WHERE b.id NOT IN ( '16', '19', '101')
        AND b.type = 'input'
        UNION ALL
        SELECT  inv.warehouse_id, inv.item_id, inv.lot_code
        FROM inventories AS inv INNER JOIN items AS itm ON inv.item_id = itm.id
        WHERE inv.inventory_transaction_id  = 16
        AND itm.lots_enabled = 1
        GROUP BY inv.warehouse_id, inv.item_id, inv.lot_code
        UNION ALL
        SELECT  inv.warehouse_id, inv.item_id, inv.lot_code
        FROM inventories AS inv INNER JOIN items AS itm ON inv.item_id = itm.id
        WHERE inv.inventory_transaction_id  = 19
        AND itm.lots_enabled = 1
        GROUP BY inv.warehouse_id, inv.item_id, inv.lot_code
        UNION ALL
        SELECT  inv.warehouse_id, inv.item_id, inv.lot_code
        FROM inventories AS inv INNER JOIN items AS itm ON inv.item_id = itm.id
        WHERE inv.inventory_transaction_id  = 101
        AND itm.lots_enabled = 1
        GROUP BY inv.warehouse_id, inv.item_id, inv.lot_code
        UNION ALL
        SELECT warehouse_id, item_id,lot_code 
        FROM production WHERE state_type_id = '03'
        GROUP BY warehouse_id, item_id,lot_code 
        UNION ALL
        SELECT warehouse_id, item_id,lot_code   FROM TMP_STOCK_LOTE_V
        UNION ALL
        SELECT warehouse_destination_id, item_id, lot_code
          FROM inventories 
          WHERE warehouse_destination_id IS NOT NULL AND lot_code IS NOT NULL
        GROUP by  warehouse_destination_id, item_id, lot_code
        UNION ALL
        SELECT warehouse_id, item_id, lot_code
          FROM inventories 
          WHERE warehouse_destination_id IS NOT NULL AND lot_code IS NOT NULL
        GROUP by  warehouse_id, item_id, lot_code
        UNION ALL
        SELECT warehouse_id, item_id, lot_code
          FROM inventories 
          WHERE lot_code IS NOT NULL
        GROUP by  warehouse_id, item_id, lot_code
        ) AS a GROUP BY warehouse_id, item_id,lot_code 
        
        ) ;
        
        
        
        UPDATE TMP_STOCK_LOTE AS a INNER JOIN
        (
        SELECT a.warehouse_id ,item_id, a.lot_code ,   SUM(quantity ) cantidad
        FROM inventories AS a INNER JOIN items AS i ON a.item_id = i.id
        WHERE inventory_transaction_id = 16
        AND  i.unit_type_id <> 'ZZ'
        AND a.lot_code IS NOT NULL
        AND CAST(a.created_at AS DATE ) <= @fecha 
        -- SELECT * FROM inventories 
        group BY a.warehouse_id, a.item_id, a.lot_code
        ) AS b ON a.item_id = b.item_id
        AND a.lot_code = b.lot_code
        AND a.warehouse_id = b.warehouse_id
        SET a.inicial = b.cantidad
        ;
        -- 
        UPDATE TMP_STOCK_LOTE AS a INNER JOIN
        (
        SELECT  b.warehouse_id, b.item_id ,b.lot_code, SUM(b.quantity) cantidad FROM purchases AS a INNER join purchase_items AS b ON a.id = b.purchase_id
        INNER JOIN items AS i ON b.item_id = i.id
        WHERE b.lot_code IS NOT NULL 
        -- AND b.item LIKE '%lots_enabled": true%'
        AND i.lots_enabled = 1
        AND a.date_of_issue >='2023-01-01'
        AND  a.date_of_issue <= @fecha 
        GROUP BY  b.warehouse_id, b.item_id ,b.lot_code
        ) AS b ON a.item_id = b.item_id
        AND a.lot_code = b.lot_code
        AND a.warehouse_id = b.warehouse_id
        SET a.compras = b.cantidad
        ;
        
        UPDATE TMP_STOCK_LOTE AS a INNER JOIN
        (
         SELECT  warehouse_id ,item_id, lot_code, sum(cast(cantidad AS DECIMAL(16,8))) AS cantidad
         FROM (
        --  SELECT  b.item_id, 
        -- REPLACE(JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[0]'),'$.code'),'"','') as lot_code ,
        -- JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[0]'),'$.compromise_quantity') AS cantidad
        --   FROM document_items AS b INNER JOIN documents AS a ON b.document_id = a.id
        -- WHERE a.date_of_issue >= '2024-01-01'
        SELECT  warehouse_id ,item_id , lot_code, SUM(cantidad) AS cantidad
        FROM (
         SELECT  b.warehouse_id , b.item_id,
         -- , b.document_id ,
        REPLACE(JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[0]'),'$.code'),'"','') as lot_code ,
        JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[0]'),'$.compromise_quantity') AS cantidad
          FROM document_items AS b INNER JOIN documents AS a ON b.document_id = a.id
        WHERE a.date_of_issue >= '2024-01-01'
        AND   a.date_of_issue <=@fecha 
        AND a.state_type_id <> 11
        -- AND item_id = 1247
        UNION all
        
         SELECT   b.warehouse_id , b.item_id,
         -- , b.document_id ,
        REPLACE(JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[1]'),'$.code'),'"','') as lot_code ,
        JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[1]'),'$.compromise_quantity') AS cantidad
          FROM document_items AS b INNER JOIN documents AS a ON b.document_id = a.id
        WHERE a.date_of_issue >= '2024-01-01'
        AND   a.date_of_issue <=@fecha 
        AND a.state_type_id <> 11
        -- AND item_id = 1247
        UNION all
        
         SELECT  b.warehouse_id ,  b.item_id,
         -- , b.document_id ,
        REPLACE(JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[2]'),'$.code'),'"','') as lot_code ,
        JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[2]'),'$.compromise_quantity') AS cantidad
          FROM document_items AS b INNER JOIN documents AS a ON b.document_id = a.id
        WHERE a.date_of_issue >= '2024-01-01'
        AND   a.date_of_issue <=@fecha 
        AND a.state_type_id <> 11
        -- AND item_id = 1247
        UNION all
        
        SELECT  b.warehouse_id , b.item_id,
        -- , b.document_id ,
        REPLACE(JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[3]'),'$.code'),'"','') as lot_code ,
        JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[3]'),'$.compromise_quantity') AS cantidad
          FROM document_items AS b INNER JOIN documents AS a ON b.document_id = a.id
        WHERE a.date_of_issue >= '2024-01-01'
        AND   a.date_of_issue <=@fecha 
        AND a.state_type_id <> 11
        -- AND item_id = 1247
        
        UNION all
         SELECT  b.warehouse_id , b.item_id,
         -- , b.document_id ,
        REPLACE(JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[4]'),'$.code'),'"','') as lot_code ,
        JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[4]'),'$.compromise_quantity') AS cantidad
          FROM document_items AS b INNER JOIN documents AS a ON b.document_id = a.id
        WHERE a.date_of_issue >= '2024-01-01'
        AND   a.date_of_issue <=@fecha 
        AND a.state_type_id <> 11
        -- AND item_id = 1247
        
        UNION all
         SELECT  b.warehouse_id , b.item_id,
         -- , b.document_id ,
        REPLACE(JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[5]'),'$.code'),'"','') as lot_code ,
        JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[5]'),'$.compromise_quantity') AS cantidad
          FROM document_items AS b INNER JOIN documents AS a ON b.document_id = a.id
        WHERE a.date_of_issue >= '2024-01-01'
        AND   a.date_of_issue <=@fecha 
        AND a.state_type_id <> 11
        -- AND item_id = 1247
        
        UNION all
         SELECT  b.warehouse_id , b.item_id,
         -- , b.document_id ,
        REPLACE(JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[5]'),'$.code'),'"','') as lot_code ,
        JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[5]'),'$.compromise_quantity') AS cantidad
          FROM document_items AS b INNER JOIN documents AS a ON b.document_id = a.id
        WHERE a.date_of_issue >= '2024-01-01'
        AND   a.date_of_issue <=@fecha 
        AND a.state_type_id <> 11
        -- AND item_id = 1247
        
        UNION all
         SELECT  b.warehouse_id , b.item_id,
         -- , b.document_id ,
        REPLACE(JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[6]'),'$.code'),'"','') as lot_code ,
        JSON_EXTRACT(JSON_EXTRACT(JSON_EXTRACT(item, '$.IdLoteSelected'  ),'$[6]'),'$.compromise_quantity') AS cantidad
          FROM document_items AS b INNER JOIN documents AS a ON b.document_id = a.id
        WHERE a.date_of_issue >= '2024-01-01'
        AND   a.date_of_issue <=@fecha 
        AND a.state_type_id <> 11
        -- AND item_id = 1247
        ) AS aa WHERE aa.lot_code IS NOT NULL 
        GROUP BY  warehouse_id , item_id , lot_code
         ) AS aa WHERE aa.lot_code IS NOT NULL 
         GROUP BY  warehouse_id , item_id, lot_code
        
        ) AS b ON a.item_id = b.item_id
        AND a.lot_code = b.lot_code
        AND a.warehouse_id  = b.warehouse_id
        SET a.ventas = b.cantidad
        
        ;
        
        
        
        
        UPDATE TMP_STOCK_LOTE AS a INNER JOIN
        (
        
        SELECT  a.warehouse_id, b.item_id, b.lot_code,  SUM(b.quantity) AS cantidad
        FROM inventories_transfer  AS a INNER JOIN
        inventories AS b ON a.id = b.inventories_transfer_id
        WHERE CAST(a.created_at AS DATE )  <= @fecha 
         GROUP BY  a.warehouse_id,  b.item_id , b.lot_code
        
         ) AS b ON a.item_id = b.item_id
        AND a.lot_code = b.lot_code
        AND a.warehouse_id = b.warehouse_id
        SET a.tr_salidas = b.cantidad
        
        ;
        
        
        UPDATE TMP_STOCK_LOTE AS a INNER JOIN
        (
        SELECT a.warehouse_destination_id AS  warehouse_id, b.item_id, b.lot_code,  SUM(b.quantity) AS cantidad
        FROM inventories_transfer  AS a INNER JOIN
        inventories AS b ON a.id = b.inventories_transfer_id
        WHERE CAST(a.created_at AS DATE )  <= @fecha 
         GROUP BY a.warehouse_destination_id  , b.item_id , b.lot_code
         ) AS b ON a.item_id = b.item_id
        AND a.warehouse_id = b.warehouse_id
        AND a.lot_code = b.lot_code
        SET a.tr_entradas = b.cantidad
        
        ;
        
        UPDATE TMP_STOCK_LOTE AS a INNER JOIN
        (
        SELECT i.warehouse_id  ,i.item_id, i.lot_code, SUM(ik.quantity) AS cantidad FROM inventories AS i INNER JOIN inventory_transactions AS b 
        ON i.inventory_transaction_id = b.id 
        INNER JOIN inventory_kardex AS ik ON i.id = ik.inventory_kardexable_id
        WHERE b.id NOT IN ( '16','19','101')
        AND b.type = 'output'
        AND CAST(i.created_at AS DATE )  <= @fecha 
        
        GROUP BY  i.warehouse_id, i.item_id, i.lot_code
         ) AS b ON a.item_id = b.item_id
        AND a.lot_code = b.lot_code
        AND a.warehouse_id = b.warehouse_id
        SET a.salidas = b.cantidad
        
        ;
        
        
        
        UPDATE TMP_STOCK_LOTE AS a INNER JOIN
        (
        SELECT i.warehouse_id,  i.item_id, i.lot_code, SUM(ik.quantity) AS cantidad FROM inventories AS i INNER JOIN inventory_transactions AS b 
        ON i.inventory_transaction_id = b.id 
        INNER JOIN inventory_kardex AS ik ON i.id = ik.inventory_kardexable_id
        WHERE b.id NOT IN ( '16', '19', '101')
        AND b.type = 'input'
        AND CAST(i.created_at AS DATE )  <= @fecha 
        -- AND comments <> 'Ajuste al físico' AND DESCRIPTION <> 'Ajuste por diferencia de inventario'
        GROUP BY  i.warehouse_id, i.item_id, i.lot_code
         ) AS b ON a.item_id = b.item_id
        AND a.lot_code = b.lot_code
        AND a.warehouse_id = b.warehouse_id
        SET a.entradas = b.cantidad
         
         ;
        
        
        UPDATE TMP_STOCK_LOTE AS a INNER JOIN
        (
        SELECT warehouse_id, lot_code , item_id, SUM(quantity) AS cantidad FROM inventories 
        WHERE inventory_transaction_id = 101
        AND CAST(created_at AS DATE )  <= @fecha 
        GROUP BY warehouse_id, lot_code , item_id
         ) AS b ON a.item_id = b.item_id
        AND a.lot_code = b.lot_code
        AND a.warehouse_id = b.warehouse_id
        SET a.salidaproduccion = b.cantidad
        
        ;
        
        UPDATE TMP_STOCK_LOTE AS a INNER JOIN
        (  SELECT warehouse_id, lot_code , item_id , SUM(quantity) AS cantidad FROM production 
        WHERE date_end  <= @fecha 
        AND  state_type_id = '03'
        
          GROUP BY warehouse_id, lot_code , item_id  
         ) AS b ON a.item_id = b.item_id
        AND a.lot_code = b.lot_code
        AND a.warehouse_id = b.warehouse_id
        SET a.entradaproduccion = b.cantidad
        
        ;
        
        
        UPDATE TMP_STOCK_LOTE
        SET stockfinal = 
         inicial+ compras+ tr_entradas - tr_salidas +  entradas + salidas - ventas + ventas_anul
         - salidaproduccion+ entradaproduccion
         ;
        
          UPDATE item_lots_group AS a 
          INNER JOIN 
           TMP_STOCK_LOTE  AS b 
            ON a.code = b.lot_code 
          AND a.item_id = b.item_id
          AND a.warehouse_id = b.warehouse_id
          SET a.quantity = b.stockfinal
          ;
        
        SELECT   a.item_id, i.internal_id , i.name AS nombre, i.description AS descripcion,  
        i.model AS modelo, i.factory_code AS codigo_fabrica , IF(i.lots_enabled = 1 , 'SI','NO') AS Usalote, IF(i.series_enabled=1,'SI','NO') AS Usaserie,
                  dc.name AS departamento ,
                  IF (cc.name = dc.name , 'N/A', cc.name) AS categoria, IF(sc.name = cc.name, 'N/A', sc.name) AS seccion, IF(fc.name = sc.name , 'N/A', fc.name) AS familia,
        a.warehouse_id, w.description, 
        a.lot_code,
         a.inicial, a.compras,a.tr_entradas AS entradas_traslados, a.tr_salidas AS salidas_traslados, a.entradas, a.salidas, a.ventas,a.ventas_anul, a.salidaproduccion,
         a.entradaproduccion ,a.stockfinal
          FROM TMP_STOCK_LOTE AS a LEFT JOIN items AS i ON a.item_id = i.id
          LEFT join (select id,
             LTRIM(RTRIM(REPLACE(REPLACE(SUBSTRING_INDEX(category_id_array, ',', 1),'[',''),']',''))) as departamento ,
            LTRIM(RTRIM(REPLACE(REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(category_id_array, ',', 2),',',-1),'[',''),']',''))) as categoria,
            LTRIM(RTRIM(REPLACE(REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(category_id_array, ',', 3),',',-1),'[',''),']',''))) as seccion,
            LTRIM(RTRIM(REPLACE(REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(category_id_array, ',', 4),',',-1),'[',''),']',''))) as familia
            from items as a ) as itc on i.id = itc.id
            left join categories as  dc on itc.departamento = dc.id
            left join categories as  cc on itc.categoria = cc.id
            left join categories as  sc on itc.seccion = sc.id
            left join categories as  fc on itc.familia = fc.id
          LEFT JOIN warehouses AS w ON a.warehouse_id = w.id
          WHERE i.lots_enabled = 1
          ORDER BY a.item_id asc
            ;
        END
        EOF;
        DB::connection('tenant')->statement($sqlCREATE);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS SP_Stock_Fecha_LoteSerie";
        DB::connection('tenant')->statement($sqlDelete);
    }
}

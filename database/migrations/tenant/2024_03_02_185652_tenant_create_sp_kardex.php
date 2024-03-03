<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantCreateSpKardex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sqlDelete = "DROP PROCEDURE IF EXISTS `SP_ReportKardex`;";
        DB::connection('tenant')->statement($sqlDelete);

        $sqlCreate = "CREATE PROCEDURE `SP_ReportKardex`(
            IN `item` INT,
            IN `warehouse` INT
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN
            SET @warehouse = warehouse;
            SET @item = item ;
            set @s  = 0.0 ;

        SELECT A.*

        FROM (
        SELECT
        i.id, i.item_id,
        inventory_transaction_id as cod_tipo_tr , i.created_at as fecha_registro,
        w1.description AS Origen,
        w2.description AS Destino,
        i.description AS Motivo,
        IF(i.inventories_transfer_id IS NULL , i.comments , it.description) AS comentario,
        '' as Estado,
         CAST(precio_perso as decimal(16,6)) AS costo ,
        CASE WHEN i.inventories_transfer_id IS NULL  THEN
        ( CASE WHEN i.production_id IS NULL THEN i.id ELSE i.production_id END )
        ELSE i.inventories_transfer_id END  AS Documento, CONCAT(p.number ,'-',p.name) AS Cliente_Proveedor ,
        production_id AS Num_orden_produccion,

         i.lot_code AS Lote_Serie,
         i.quantity AS Cantidad, 0.0 AS saldo
        FROM inventories AS i INNER JOIN inventory_kardex AS ik ON i.id = ik.inventory_kardexable_id AND i.warehouse_id = ik.warehouse_id
        INNER JOIN items AS itt ON i.item_id = itt.id
        LEFT JOIN warehouses AS w1 ON i.warehouse_id = w1.id
        LEFT JOIN warehouses AS w2 ON i.warehouse_destination_id = w2.id
        LEFT JOIN inventories_transfer AS it ON i.inventories_transfer_id = it.id
        LEFT JOIN persons AS p ON it.client_id = p.id
        WHERE ik.inventory_kardexable_type LIKE '%Inventory%'
         AND i.item_id = @item
         AND i.warehouse_id = @warehouse

        UNION ALL
        -- compras
         SELECT ik.id,ik.item_id  ,NULL AS cod_tipo_tr, ik.created_at AS fecha , w.description Origen, NULL AS Destino, dt.description AS Motivo ,
           p.observation AS comentario,CONCAT(imp.numeroImportacion,'-',imp.estado)  AS Estado, pri.unit_price AS Precio ,
           CONCAT(p.series,'-',p.number,'-',p.sequential_number) AS Documento,
         CONCAT(pr.number,'-',pr.name) AS Cliente_proveedor , NULL AS Num_orden_produccion, pri.lot_code AS Lote_serie, pri.quantity AS Cantidad ,
           0.0 AS saldo
          FROM inventory_kardex  AS ik INNER JOIN purchases AS p ON ik.inventory_kardexable_id = p.id
        INNER JOIN items AS itt ON ik.item_id = itt.id
         INNER JOIN purchase_items AS pri ON p.id = pri.purchase_id
         LEFT JOIN persons AS pr ON p.supplier_id = p.id
         LEFT JOIN warehouses AS w ON pri.warehouse_id = w.id
         LEFT JOIN cat_purchase_document_types2 AS dt ON  p.document_type_intern = dt.idType
         LEFT JOIN import AS imp ON p.import_id = imp.id
         WHERE
         ik.inventory_kardexable_type LIKE '%Purchase'
         AND dt.stock = 1
         AND ik.item_id = @item
         AND ik.warehouse_id = @warehouse


        -- ventas
        UNION ALL
         SELECT pri.id,  pri.item_id  ,NULL AS cod_tipo_tr, pr.created_at AS fecha , w.description Origen, NULL AS Destino, dt.description AS Motivo,
        '' AS comentario,''   AS Estado, pri.unit_price AS Precio ,
          CONCAT(p.series,'-',p.number) AS Documento,
        CONCAT(pr.number,'-',pr.name) AS Cliente_proveedor , NULL AS Num_orden_produccion, '' AS Lote_serie, pri.quantity AS Cantidad ,
          0.0 AS saldo
          FROM
          documents AS p
         INNER JOIN document_items AS pri ON p.id = pri.document_id
         INNER JOIN items AS itt ON pri.item_id = itt.id
         LEFT JOIN persons AS pr ON p.customer_id = p.id
         LEFT JOIN warehouses AS w ON pri.warehouse_id = w.id
         LEFT JOIN cat_document_types AS dt ON p.document_type_id = dt.id
         WHERE

          pri.item_id = @item
         AND pri.warehouse_id = @warehouse
         ) AS A
         ORDER BY A.fecha_registro asc
         ;
        END";

        DB::connection('tenant')->statement($sqlCreate);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

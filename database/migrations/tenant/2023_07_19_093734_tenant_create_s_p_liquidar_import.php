<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantCreateSPLiquidarImport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection("tenant")->statement("DROP PROCEDURE IF EXISTS SP_Liquidarimportacion;");
        DB::connection("tenant")->statement("CREATE PROCEDURE `SP_Liquidarimportacion`(
            IN `id` INT
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT 'Se actualiza el costo de la importacion y se muestrta el valor total de la importacion'
        BEGIN

                set @importacion = id ;


                DROP TABLE IF EXISTS TMP_IMP1;
                CREATE TEMPORARY TABLE TMP_IMP1
                AS (
                SELECT
                a.series, a.number, e.numeroImportacion importacion,
                 0 Numerolinea, b.item_id AS codarticulo, c.internal_id AS referencia, c.name descripcion,
                d.tariff partidaarancelaria, d.advaloren AS porcentaje ,d.fodinfa AS porcentajef , b.quantity as unidadestoal ,
                b.unit_value AS fob,(b.unit_value*b.quantity ) AS fobtotal,

                   CAST(0.0 AS DECIMAL(12,4)) AS gastohastafob,

                  CAST(0.0 AS DECIMAL(12,4)) As fob_finalunit,
                CAST(0.0 AS DECIMAL(12,4)) As fob_finaltotal,
                    CAST( 0.0  AS DECIMAL(12,4)) AS fletetotal,
                 CAST(0.0 AS DECIMAL(12,4)) AS segurototal,
                CAST( 0.0 AS DECIMAL(12,4)) AS  cif,
                CAST(0.0 AS DECIMAL(12,4)) AS valoradvaloren,
                CAST(0.0 AS DECIMAL(12,4)) AS fodinfa,
                 CAST(0.0 AS DECIMAL(12,4)) AS Ice,
                CAST(0.0 AS DECIMAL(12,4) )AS iva,
                CAST(0.0 AS DECIMAL(12,4) )AS total_impuestos_tributos,
                CAST( 0.0 AS DECIMAL(12,4)) AS interes,
                CAST( 0.0 AS DECIMAL(12,4)) AS interestotal,
                CAST(0.0 AS DECIMAL(12,4)) AS gastos ,
                CAST(0.0 AS DECIMAL(12,4) )AS isd,
                CAST(0.0 AS DECIMAL(12,4) )AS comunicaciones,
                CAST(0.0 AS DECIMAL(12,4) )AS totalgastos,
                  CASt(0.0 AS DECIMAL(12,4) ) AS FACTOR,
                 CAST(0.0 AS DECIMAL(12,4) ) AS costo_unitario,
                CAST(0.0 AS DECIMAL(12,4) ) AS costototal


                 FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                 LEFT JOIN items AS c ON b.item_id = c.id
                 LEFT JOIN tariffs AS d ON c.tariff_id = d.id
                 LEFT JOIN import AS e ON a.import_id = e.id
                 WHERE  a.import_id =  @importacion
                 AND c.unit_type_id <> 'ZZ'
                 );

                     SET @fob = ((SELECT CASE WHEN SUM(b.total_value) IS NULL then 0 ELSE SUM(b.total_value) END AS fob
                  FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                  inner JOIN items AS c ON b.item_id = c.id
                WHERE a.import_id =  9
                 AND c.unit_type_id <> 'ZZ' ))  ;

                  SET @udst = ((SELECT CASE WHEN SUM(b.quantity) IS NULL then 0 ELSE SUM(b.quantity) END AS udst
                  FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                  inner JOIN items AS c ON b.item_id = c.id
                WHERE a.import_id =  9
                 AND c.unit_type_id <> 'ZZ' ))  ;

                SET @hastafob = ( ( SELECT  case when  SUM(b.quantity*b.unit_value) IS NULL then 0 ELSE SUM(b.quantity*b.unit_value) END AS hastafob
                 FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                 INNER JOIN items AS c ON b.Item_id = c.id
                WHERE  a.import_id =  @importacion
                AND c.concept_id = 7 ) ) ;

                SET @flete = ( ( SELECT  CASE WHEN  SUM(b.quantity*b.unit_value) IS NULL then 0 ELSE  SUM(b.quantity*b.unit_value) END AS flete
                  FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                 INNER JOIN items AS c ON b.Item_id = c.id
                WHERE  a.import_id =  @importacion
                AND c.concept_id = 4))  ;

                       SET @interes = ((SELECT  CASE WHEN SUM(b.quantity*b.unit_value) IS NULL THEN 0 ELSE SUM(b.quantity*b.unit_value) END AS interes
                 FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                 INNER JOIN items AS c ON b.Item_id = c.id
                WHERE  a.import_id =  @importacion
                AND c.concept_id = 8))  ;


                SET @seguro = ((SELECT  case when SUM(b.quantity*b.unit_value) IS NULL then 0 ELSE SUM(b.quantity*b.unit_value) END AS seguro
                 FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                 INNER JOIN items AS c ON b.Item_id = c.id
                WHERE  b.import =  @importacion
                AND c.concept_id = 5 )) ;

                SET @gastos = ((SELECT  CASE WHEN  SUM(b.total_value) IS NULL then 0 ELSE SUM(b.total_value)  END AS gastos
                 FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                 INNER JOIN items AS c ON b.Item_id = c.id
                WHERE  b.import  =  @importacion
                AND c.concept_id IN( 1 , 6))) ;

                  SET @isd = ((SELECT  CASE WHEN  SUM(b.total_value) IS NULL then 0 ELSE SUM(b.total_value)  END AS gastos
                 FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                 INNER JOIN items AS c ON b.Item_id = c.id
                WHERE  b.import  =  @importacion
                AND c.concept_id = 3 )) ;

                SET @comunicaciones = ((SELECT  CASE WHEN  SUM(b.total_value) IS NULL then 0 ELSE SUM(b.total_value)  END AS gastos
                 FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                 INNER JOIN items AS c ON b.Item_id = c.id
                WHERE  b.import  =  @importacion
                AND c.concept_id = 9 )) ;

                UPDATE TMP_IMP1
                SET isd =  CASE WHEN @fob <=0 THEN 0 ELSE  ( (@isd/@fob ) * (fob))*unidadestoal END  ;

                  UPDATE TMP_IMP1
                SET comunicaciones =  CASE WHEN @fob <=0 THEN 0 ELSE  ( (@comunicaciones/@udst ) )*unidadestoal END  ;

                UPDATE TMP_IMP1
                SET gastohastafob =  CASE WHEN @fob <=0 THEN 0 ELSE  (@hastafob/@fob ) * (fob*unidadestoal) END
                ;

                UPDATE TMP_IMP1
                SET  fob_finaltotal = fobtotal  + gastohastafob
                ;

                UPDATE TMP_IMP1
                SET fob_finalunit =  fob_finaltotal/unidadestoal
                ;

               --  UPDATE TMP_IMP1
        --         SET flete =  CASE WHEN @fob <=0 THEN 0 ELSE   (@flete/@fob ) * (fob) END
        --         ;

                UPDATE TMP_IMP1
                SET fletetotal = CASE WHEN @fob <=0 THEN 0 ELSE  ( (@flete/@fob ) * (fob))*unidadestoal END
                ;

                -- UPDATE TMP_IMP1
        --         SET seguro = CASE WHEN @fob <=0  THEN 0 ELSE  (@seguro/@fob ) * (fob) END
        --         ;

                UPDATE TMP_IMP1
                SET segurototal = CASE WHEN @fob <=0  THEN 0 ELSE ( (@seguro/@fob ) * (fob))*unidadestoal END
                ;



                UPDATE TMP_IMP1
                SET cif =  fob_finaltotal+fletetotal+segurototal
                ;

                UPDATE TMP_IMP1
                SET valoradvaloren =ISNULL( cif*(porcentaje/100))
                ;

                UPDATE TMP_IMP1
                SET fodinfa = (cif*(porcentajef/100))
                ;

                UPDATE TMP_IMP1
                SET iva =( (cif+valoradvaloren+fodinfa+ice)*0.12)
                ;

                UPDATE TMP_IMP1
                SET total_impuestos_tributos = ( valoradvaloren+ fodinfa+ice+iva )
                ;



        --       UPDATE TMP_IMP1
        --         SET interes =  CASE WHEN @costot <=0  THEN 0 ELSE ( (@interes/@costot) * costo)/unidadestoal END
        --         ;
        --
        --         UPDATE TMP_IMP1
        --         SET interestotal =  interes * unidadestoal
        --         ;

                UPDATE TMP_IMP1
                SET gastos = CASE WHEN @fob <=0 THEN 0 ELSE  ((@gastos/@fob ) * (fob))  * unidadestoal END
                ;

                  UPDATE TMP_IMP1
                SET totalgastos = ( interestotal +gastos + isd + comunicaciones)
                ;

        --
                UPDATE TMP_IMP1
                SET costo_unitario = (cif+valoradvaloren+fodinfa+ice+gastos)/unidadestoal
                ;
        --
        --
        --
               UPDATE TMP_IMP1
               SET costototal = costo_unitario*unidadestoal ;
        --

        --
        --         UPDATE TMP_IMP1
        --         SET costocalculado =  costo+interes
        --         ;
        --
        --
        --         UPDATE TMP_IMP1
        --         SET totallinea = (costo+interes) *unidadestoal
        --         ;
        --
        --
        --         UPDATE TMP_IMP1
        --         SET FACTOR = (totallinea/(fob*unidadestoal))
        --         ;
        --


                -- SELECT * FROM TMP_IMP1 ;


                UPDATE items AS a
                INNER JOIN TMP_IMP1 AS b
                ON a.id = b.codarticulo
                SET purchase_unit_price = b.costo_unitario
                ;

                SELECT SUM(costototal) AS totalimportacion FROM TMP_IMP1 ;



                END");

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

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantCreateSPImportaciones extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection("tenant")->statement("DROP PROCEDURE IF EXISTS SP_Reporteimportacion;");
        DB::connection("tenant")->statement("CREATE PROCEDURE `SP_Reporteimportacion`(
            IN `id` INT
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT 'Genera el reporte de importaciópn que se mostrara en excel'
        BEGIN

                 set @importacion = id ;

                 SET @hastafob = ( SELECT CASE WHEN SUM(FOB) IS NULL THEN 0 ELSE  CAST(SUM(FOB) AS DECIMAL(16,6)) END AS FOB FROM import AS IM WHERE IM.ID = @importacion ) ;
                 SET @flete = ( SELECT CASE WHEN SUM(FLETE) IS NULL THEN 0 ELSE CAST(SUM(FLETE) AS DECIMAL(16,6)) END AS FLETE FROM import AS IM WHERE IM.ID = @importacion ) ;


                SET @icotemr = ( SELECT incoterm FROM import WHERE id = @importacion  LIMIT 1 ) ;

                DROP TABLE IF EXISTS TMP_IMP1;
                DROP TABLE IF EXISTS TMP_IMP2;


                CREATE TEMPORARY TABLE TMP_IMP1
                AS (
                SELECT
                a.series, a.number, e.numeroImportacion importacion,
                 0 Numerolinea, b.item_id AS codarticulo, c.internal_id AS referencia, c.name descripcion,
                d.tariff partidaarancelaria, d.advaloren AS porcentaje ,d.fodinfa AS porcentajef , b.quantity as unidadestoal ,
                  CASE WHEN a.exchange_rate_sale = 1 THEN 0 ELSE CAST( ( CAST((b.unit_value*b.quantity ) AS DECIMAL(12,6)) / a.exchange_rate_sale ) AS DECIMAL(12,3)) END AS Eur,
                CAST(b.unit_value AS DECIMAL(16,6)) AS fob,CAST( (b.unit_value*b.quantity ) AS DECIMAL(12,2)) AS fobtotal,

                   CAST(0.0 AS DECIMAL(12,4)) AS gastohastafob,
                   CAST(0.0 AS DECIMAL(12,4)) AS empaque,
                   CAST(0.0 AS DECIMAL(12,4)) AS fleteimportacion,
                    CAST(0.0 AS DECIMAL(12,4)) AS nogastos,
                    CAST(0.0 AS DECIMAL(12,4)) AS nogastohastafob,
                CAST(0.0 AS DECIMAL(12,4)) As fob_finalunit,
                CAST(0.0 AS DECIMAL(12,4)) As fob_finaltotal,
                  CAST( 0.0  AS DECIMAL(12,4)) AS fletetotal,
                CAST( 0.0  AS DECIMAL(12,4)) AS nofletetotal,
                    CAST(0.0 AS DECIMAL(12,4)) AS segurototal,
                CAST( 0.0 AS DECIMAL(12,6)) AS  cif,
                CAST(0.0 AS DECIMAL(12,4)) AS valoradvaloren,
                CAST(0.0 AS DECIMAL(12,4)) AS fodinfa,
                 CAST(0.0 AS DECIMAL(12,4)) AS Ice,
                CAST(0.0 AS DECIMAL(12,4) )AS iva,
                CAST(0.0 AS DECIMAL(12,4) )AS total_impuestos_tributos,
                CAST( 0.0 AS DECIMAL(12,4)) AS interes,
                CAST( 0.0 AS DECIMAL(12,4)) AS interestotal,
                CAST(0.0 AS DECIMAL(12,4)) AS gastos ,
                CAST(0.0 AS DECIMAL(12,4) )AS isd,
                CAST(0.0 AS DECIMAL(12,8) )AS comunicaciones,
                CAST(0.0 AS DECIMAL(12,6) )AS totalgastos,
                  CASt(0.0 AS DECIMAL(12,8) ) AS FACTOR,
                 CAST(0.0 AS DECIMAL(12,4) ) AS costo_unitario,
                CAST(0.0 AS DECIMAL(12,4) ) AS costototal


                 FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                 LEFT JOIN items AS c ON b.item_id = c.id
                 LEFT JOIN tariffs AS d ON c.tariff_id = d.id
                 LEFT JOIN import AS e ON a.import_id = e.id
                 WHERE  a.import_id =  @importacion
                 AND c.unit_type_id <> 'ZZ'
                 AND a.tipo_doc_id = 1
                 );


                CREATE TEMPORARY TABLE TMP_IMP2
                AS (
                SELECT   CASE WHEN  isd IS NULL THEN CAST( 0.0 AS DECIMAL (12,8)) ELSE CAST( isd AS DECIMAL(12,8)) END AS ISD,
                CASE WHEN  comunications IS NULL THEN CAST( 0.0 AS DECIMAL (12,8)) ELSE CAST( comunications AS DECIMAL(12,8)) END AS comunications
                FROM import AS I WHERE I.id =  @importacion
                );

                  SET @tc = ( SELECT exchange_rate_sale FROM purchases WHERE import_id =  @importacion  AND tipo_doc_id = 1 LIMIT 1 );

                     SET @fob = ((SELECT CASE WHEN  SUM(CAST(( quantity * unit_value ) AS DECIMAL ( 16,2))) IS NULL then 0 ELSE  SUM(CAST(( quantity * unit_value ) AS DECIMAL ( 16,2)))  END AS fob
                  FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                  inner JOIN items AS c ON b.item_id = c.id
                WHERE a.import_id =  @importacion
                 AND c.unit_type_id <> 'ZZ'
                    AND a.tipo_doc_id = 1 ))  ;

                  SET @udst = ((SELECT CASE WHEN SUM(b.quantity) IS NULL then 0 ELSE SUM(b.quantity) END AS udst
                  FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                  inner JOIN items AS c ON b.item_id = c.id
                WHERE a.import_id =  @importacion
                 AND c.unit_type_id <> 'ZZ' ))  ;

                  SET @empaque = CAST((SELECT  CASE WHEN SUM(b.quantity*b.unit_value) IS NULL THEN 0 ELSE SUM(b.quantity*b.unit_value) END AS interes
                 FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                 INNER JOIN items AS c ON b.Item_id = c.id
                WHERE  a.import_id =  @importacion
                AND a.tipo_doc_id = 1
                AND c.concept_id = 7 ) AS decimal(16,6))  ;

                  SET @fleteimportacion = CAST((SELECT  CASE WHEN SUM(b.quantity*b.unit_value) IS NULL THEN 0 ELSE SUM(b.quantity*b.unit_value) END AS interes
                 FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                 INNER JOIN items AS c ON b.Item_id = c.id
                WHERE  a.import_id =  @importacion
                AND a.tipo_doc_id = 1
                AND c.concept_id = 4 ) AS decimal(16,6))  ;


                  SET @nohastafob = 0 ;


                             UPDATE TMP_IMP1
                SET gastohastafob =  CASE WHEN @fob <=0 THEN 0 ELSE  (@hastafob/@fob ) * (fob*unidadestoal) END
                ;

                 UPDATE TMP_IMP1
                SET empaque =  CASE WHEN @fob <=0 THEN 0 ELSE  (@empaque/@fob ) * (fob*unidadestoal) END
                ;

                  UPDATE TMP_IMP1
                SET fleteimportacion =  CASE WHEN @fob <=0 THEN 0 ELSE  (@fleteimportacion/@fob ) * (fob*unidadestoal) END
                ;


                SET @nogastos = 0 ;


                  UPDATE TMP_IMP1
                SET nogastos =  0 ;


                UPDATE TMP_IMP1
                SET  fob_finaltotal = CAST( fobtotal  AS DECIMAL(16,2)) +  CAST( gastohastafob AS DECIMAL(16,2)) +  CAST( empaque AS DECIMAL(16,2))
                ;

                UPDATE TMP_IMP1
                SET fob_finalunit =   CAST( fob_finaltotal AS DECIMAL(16,2)) /unidadestoal
                ;

                SET @fobfinaltotal = ( SELECT SUM(fob_finaltotal) AS f FROM TMP_IMP1   ) ;



                SET @noflete = 0 ;


                   SET @fodinfa = CAST((SELECT  CASE WHEN  SUM(b.total_value) IS NULL then 0 ELSE SUM(CAST( b.total_value AS DECIMAL(16,2)))  END AS gastos
                 FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                 INNER JOIN items AS c ON b.Item_id = c.id
                WHERE  b.import  =  @importacion
                AND c.concept_id IN( 12)
                      ) AS decimal(16,6))  ;

                       SET @advaloren = CAST((SELECT  CASE WHEN  SUM(b.total_value) IS NULL then 0 ELSE SUM(CAST( b.total_value AS DECIMAL(16,2)))  END AS gastos
                 FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                 INNER JOIN items AS c ON b.Item_id = c.id
                WHERE  b.import  =  @importacion
                AND c.concept_id IN( 13)
                      ) AS decimal(16,6))  ;


                 SET @iva = CAST((SELECT  CASE WHEN  SUM(b.total_value) IS NULL then 0 ELSE SUM(CAST( b.total_value AS DECIMAL(16,2)))  END AS gastos
                 FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                 INNER JOIN items AS c ON b.Item_id = c.id
                WHERE  b.import  =  @importacion
                AND c.concept_id IN( 14)
                      ) AS decimal(16,6))  ;




                       SET @interes = CAST((SELECT  CASE WHEN SUM(b.quantity*b.unit_value) IS NULL THEN 0 ELSE SUM(b.quantity*b.unit_value) END AS interes
                 FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                 INNER JOIN items AS c ON b.Item_id = c.id
                WHERE  a.import_id =  @importacion
                AND c.concept_id = 8 ) AS decimal(16,2))  ;


                SET @seguro = ((SELECT  case when SUM(b.quantity*b.unit_value) IS NULL then 0 ELSE SUM(b.quantity* cast(b.unit_value AS DECIMAL(16,2))) END AS seguro
                 FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                 INNER JOIN items AS c ON b.Item_id = c.id
                WHERE  b.import =  @importacion
                AND c.concept_id = 5 )) ;

                SET @gastos = ((SELECT  CASE WHEN  SUM(b.total_value) IS NULL then 0 ELSE SUM(CAST( b.total_value AS DECIMAL(16,2)))  END AS gastos
                 FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                 INNER JOIN items AS c ON b.Item_id = c.id
                WHERE  b.import  =  @importacion
                AND c.concept_id IN( 1 , 6 , 5 ,10,11 ,4)
                  AND a.tipo_doc_id = 2 )
               ) ;


               SET @gastos = @gastos - @noflete ;

                SET @comunicaciones = (  SELECT comunications from TMP_IMP2 ) ;

                 SET @fobas =  (
                                  SELECT
                                  SUM(CAST((b.unit_value*b.quantity ) AS DECIMAL(12,2))) AS fobtotal
                                 FROM purchases AS a INNER JOIN purchase_items AS b on
                            a.id = b.purchase_id
                            INNER JOIN items AS c ON b.Item_id = c.id
                            WHERE a.import_id =   @importacion
                            AND a.tipo_doc_id = 1
                            AND CASE WHEN c.concept_id  IS NULL THEN 0 ELSE  c.concept_id  END  <>  8 )
                                ;

                -- base para comunicaciones
                        SET @interesas = (CAST((SELECT  CASE WHEN SUM(b.quantity*b.unit_value) IS NULL THEN 0 ELSE SUM(b.quantity* CAST( b.unit_value AS DECIMAL(16,2))) END AS interes
                            FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
                            INNER JOIN items AS c ON b.Item_id = c.id
                            WHERE  a.import_id =  @importacion
                            AND c.concept_id = 8 ) AS DECIMAL(16,2)))  ;
                 -- porcentaje isd
                       SET @porisd =  (  SELECT isd from TMP_IMP2 ) ;
                            -- CAST( 3.5 AS decimal(12,4)) ;
                            -- (SELECT    isd FROM import WHERE id =  @importacion  LIMIT 1 ) ;

                     SET @isd =  CAST( (( @fobas +  @interesas  ) *  ( @porisd/100) )  AS DECIMAL(16,8)) ;




                UPDATE TMP_IMP1
                SET isd =  fobtotal  * ( (   CAST( ( @isd * 100  ) /  @fob AS DECIMAL(12,8)))/100) ;

                  UPDATE TMP_IMP1
                SET comunicaciones =

                 ( fobtotal )  *
                  ( ((   CAST( ( @comunicaciones * 100  ) /  @fob AS DECIMAL(12,8)))/100 )) ;





                UPDATE TMP_IMP1
                SET fletetotal = CASE WHEN @fobfinaltotal <=0 THEN 0 ELSE  ( (@flete/@fobfinaltotal ) * (fob_finaltotal)) END
                ;


                UPDATE TMP_IMP1
                SET nofletetotal = CASE WHEN @fobfinaltotal <=0 THEN 0 ELSE  ( (@noflete/@fobfinaltotal ) * (fob_finaltotal)) END
                ;



                UPDATE TMP_IMP1
                SET segurototal = CASE WHEN @fob <=0  THEN 0 ELSE ( (@seguro/@fobfinaltotal ) * (fob_finaltotal)) END
                ;



                UPDATE TMP_IMP1
                SET cif = CAST(fobtotal+fletetotal+segurototal+empaque+gastohastafob AS decimal(16,2))
                  --  fob_finaltotal+fletetotal+segurototal+empaque
                ;


                SET @baseadvaloren = (SELECT SUM(CASE WHEN ( porcentaje IS NULL OR porcentaje = 0 ) THEN 0 ELSE   (fob_finaltotal +segurototal) END ) FROM TMP_IMP1)        ;

                SET @basefodinfa =(SELECT SUM(CASE WHEN ( porcentajef IS NULL OR porcentajef = 0 ) THEN 0 ELSE   cif END ) FROM TMP_IMP1)        ;



        --         UPDATE TMP_IMP1 SET valoradvaloren = CASE WHEN porcentaje IS NULL THEN 0 ELSE  ( (  fob_finaltotal +segurototal)* (porcentaje/100)) END ;

                UPDATE TMP_IMP1 SET valoradvaloren = CASE WHEN ( porcentaje IS NULL OR porcentaje = 0 ) THEN 0 ELSE  ( (  fob_finaltotal +segurototal)* (  @advaloren/@baseadvaloren)) END ;
        --         UPDATE TMP_IMP1 SET fodinfa = CASE WHEN porcentajef IS NULL THEN 0 ELSE   ( (cif*(porcentajef/100))) END ;
                 UPDATE TMP_IMP1 SET fodinfa = CASE WHEN ( porcentajef IS NULL OR porcentajef = 0 ) THEN 0 ELSE  ( (cif)* (  @fodinfa/@basefodinfa)) END ;

                SET @baseiva = (SELECT  SUM(cif+valoradvaloren+fodinfa+ice) FROM TMP_IMP1 )  ;
        --        UPDATE TMP_IMP1 SET iva =( (cif+valoradvaloren+fodinfa+ice)*0.12) ;
                 UPDATE TMP_IMP1 SET iva = ((cif+valoradvaloren+fodinfa+ice) * (@iva/@baseiva)) ;

                UPDATE TMP_IMP1
                SET total_impuestos_tributos = ( valoradvaloren+ fodinfa+ice )
                ;



                UPDATE TMP_IMP1
                SET gastos = CASE WHEN @fob <=0 THEN 0 ELSE  ((@gastos/@fob ) * (fob))  * unidadestoal END
                ;

                 UPDATE TMP_IMP1
                SET interestotal =  CASE WHEN @fob <=0 THEN 0 ELSE  ( (@interes/@fob ) * (fobtotal)) END  ;


                 UPDATE TMP_IMP1
                SET interes = interestotal/unidadestoal  ;


                  UPDATE TMP_IMP1
                SET totalgastos = ( fobtotal + empaque + fleteimportacion  +  valoradvaloren +  fodinfa + gastos +  interestotal  + isd + comunicaciones   )
                  -- ( fobtotal +   valoradvaloren +  fodinfa + gastos +  interestotal  + isd + comunicaciones   + gastohastafob -  nogastohastafob   - nogastos)
                ;



                 SET @FACTORIAD = CAST(	(	SELECT   CASE WHEN SUM(Eur) = 0 THEN
                   Sum(totalgastos) / SUM(fobtotal)  ELSE  SUM(totalgastos) / SUM(Eur) END
                  FROM TMP_IMP1 ) AS DECIMAL(12,5)) ;

                  SET @FACTORSAD = CAST(	(	SELECT   CASE WHEN SUM(Eur) = 0 THEN
                   Sum(totalgastos-valoradvaloren ) / SUM(fobtotal)  ELSE  SUM(totalgastos-valoradvaloren ) / SUM(Eur) END
                  FROM TMP_IMP1 ) AS DECIMAL(12,5)) ;

                 UPDATE TMP_IMP1
                 SET FACTOR =   @FACTORIAD
                 WHERE valoradvaloren > 0
                 ;

                  UPDATE TMP_IMP1
                 SET FACTOR =   @FACTORSAD
                 WHERE valoradvaloren =  0
                 ;

                    UPDATE TMP_IMP1
                SET costototal = CASE WHEN Eur = 0 THEN ( (fobtotal )   * @FACTORSAD  ) ELSE  ( ( Eur  ) * @FACTORSAD  ) END
                 WHERE valoradvaloren =  0

                ;
        --
                 UPDATE TMP_IMP1
                SET costototal = CASE WHEN Eur = 0 THEN ( (fobtotal  * @FACTORSAD  )  + valoradvaloren ) ELSE  ( ( Eur  * @FACTORSAD ) + valoradvaloren  ) END
                 WHERE valoradvaloren >  0
                 ;
                UPDATE TMP_IMP1
                SET costototal  = totalgastos ;

               UPDATE TMP_IMP1
               SET costo_unitario = costototal / unidadestoal ;



                SELECT series,number,importacion,Numerolinea,codarticulo,referencia,descripcion,partidaarancelaria,porcentaje,porcentajef,unidadestoal,Eur,fob,fobtotal,
                  empaque, gastohastafob,fob_finalunit,fob_finaltotal,fletetotal,segurototal,cif,valoradvaloren,fodinfa,Ice,iva,total_impuestos_tributos,interes,interestotal,gastos,isd,comunicaciones,totalgastos,FACTOR,costo_unitario,costototal FROM TMP_IMP1 ;


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

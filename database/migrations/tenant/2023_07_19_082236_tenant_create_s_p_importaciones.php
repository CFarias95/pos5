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


        SET @fob = (SELECT SUM(b.total_value)
         FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
          inner JOIN items AS c ON b.item_id = c.id
        WHERE a.import_id =  @importacion
         AND c.unit_type_id <> 'ZZ' )  ;

        SET @hastafob = ( SELECT SUM(b.total_value)
         FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
         INNER JOIN items AS c ON b.Item_id = c.id
        WHERE  a.import_id =  @importacion
        AND c.concept_id = 7 )  ;

        SET @flete = ( SELECT SUM(b.total_value)
         FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
         INNER JOIN items AS c ON b.Item_id = c.id
        WHERE  a.import_id =  @importacion
        AND c.concept_id = 4)  ;

        SET @interes = (SELECT SUM(b.total_value)
         FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
         INNER JOIN items AS c ON b.Item_id = c.id
        WHERE  a.import_id =  @importacion
        AND c.concept_id = 8)  ;


        SET @seguro = ISNULL((SELECT SUM(b.total_value)
         FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
         INNER JOIN items AS c ON b.Item_id = c.id
        WHERE  b.import =  @importacion
        AND c.concept_id = 5) ) ;

        SET @gastos = ISNULL((SELECT SUM(b.total_value)
         FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
         INNER JOIN items AS c ON b.Item_id = c.id
        WHERE  b.import  =  @importacion
        AND c.concept_id = 1))  ;


        DROP TABLE IF EXISTS TMP_IMP1;
        CREATE TEMPORARY TABLE TMP_IMP1
        AS (
        SELECT
        a.series, a.number, e.numeroImportacion importacion,
         0 Numerolinea, b.item_id AS codarticulo, c.internal_id AS referencia, c.name descripcion,
        d.tariff partidaarancelaria, d.advaloren AS porcentaje ,d.fodinfa AS porcentajef , b.quantity as unidadestoal ,
        b.unit_value AS fob, CAST(0.0 AS DECIMAL(12,4)) AS gastohastafob,
        CAST(0.0 AS DECIMAL(12,4)) As nuevofob, (b.unit_value*b.quantity ) AS fobtotal,
        CAST( 0.0 AS DECIMAL(12,4)) AS flete, CAST( 0.0  AS DECIMAL(12,4)) AS fletetotal,
        CAST( 0.0 AS DECIMAL(12,4)) AS seguro,
        CAST(0.0 AS DECIMAL(12,4)) AS segurototal,
        CAST( 0.0 AS DECIMAL(12,4)) AS  cif,
        CAST(0.0 AS DECIMAL(12,4)) AS valoradvaloren,
        CAST(0.0 AS DECIMAL(12,4)) AS fodinfa,
         CAST(0.0 AS DECIMAL(12,4)) AS Ice,
        CAST(0.0 AS DECIMAL(12,4) )AS iva,
        CAST(0.0 AS DECIMAL(12,4)) AS gastos ,
        CAST(0.0 AS DECIMAL(12,4) )AS gastostotal,
        CAST(0.0 AS DECIMAL(12,4) ) AS costo,
        CAST( 0.0 AS DECIMAL(12,4)) AS interes,
        CAST( 0.0 AS DECIMAL(12,4)) AS interestotal,
        CAST(0.0 AS DECIMAL(12,4) ) AS costocalculado,
        CAST( 0.0  AS DECIMAL(12,4)) AS totallinea,
        CASt(0.0 AS DECIMAL(12,4) ) AS factor
         FROM purchases AS a INNER JOIN purchase_items AS b ON a.id = b.purchase_id
         LEFT JOIN items AS c ON b.item_id = c.id
         LEFT JOIN tariffs AS d ON c.tariff_id = d.id
         LEFT JOIN import AS e ON a.import_id = e.id
         WHERE  a.import_id =  @importacion
         AND c.unit_type_id <> 'ZZ'
         );



        UPDATE TMP_IMP1
        SET gastohastafob =  (@hastafob/@fob ) * (fob*unidadestoal)
        ;

        UPDATE TMP_IMP1
        SET nuevofob =  fob + (gastohastafob/unidadestoal)
        ;

        UPDATE TMP_IMP1
        SET fobtotal =  nuevofob*unidadestoal
        ;

        UPDATE TMP_IMP1
        SET flete =  (@flete/@fob ) * (fob)
        ;

        UPDATE TMP_IMP1
        SET fletetotal =  flete * unidadestoal
        ;

        UPDATE TMP_IMP1
        SET seguro =  (@seguro/@fob ) * (fob)
        ;

        UPDATE TMP_IMP1
        SET segurototal =  seguro * unidadestoal
        ;



        UPDATE TMP_IMP1
        SET cif =  nuevofob+flete+seguro
        ;

        UPDATE TMP_IMP1
        SET valoradvaloren = cif*(porcentaje/100)
        ;

        UPDATE TMP_IMP1
        SET fodinfa = cif*(porcentajef/100)
        ;

        UPDATE TMP_IMP1
        SET iva = (cif+valoradvaloren+fodinfa+ice)*0.12
        ;

        UPDATE TMP_IMP1
        SET gastos =  (@gastos/@fob ) * (fob)
        ;

        UPDATE TMP_IMP1
        SET gastostotal =  gastos * unidadestoal
        ;

        UPDATE TMP_IMP1
        SET costo = cif+valoradvaloren+fodinfa+ice+gastos
        ;




        SET @costot = (SELECT SUM(Costo) AS C FROM TMP_IMP1 );

        UPDATE TMP_IMP1
        SET interes = ( (@interes/@costot) * costo)/unidadestoal
        ;

        UPDATE TMP_IMP1
        SET interestotal =  interes * unidadestoal
        ;

        UPDATE TMP_IMP1
        SET costocalculado =  costo+interes
        ;


        UPDATE TMP_IMP1
        SET totallinea = (costo+interes) *unidadestoal
        ;

        SELECT * FROM TMP_IMP1 ;


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

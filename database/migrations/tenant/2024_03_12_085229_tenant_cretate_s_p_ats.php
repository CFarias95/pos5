<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantCretateSPAts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sqlAts1 = "CREATE PROCEDURE `SP_INFORMANTE`(
            IN `d` DATE,
            IN `h` DATE
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT 'Datos de informate para ATS'
        BEGIN
        SET @desde =d ;
        SET @hasta =h ;

        SELECT 'R' AS TipoIDInformante ,  number AS IdInformante, NAME AS razonSocial,
        YEAR(@desde) AS Anio,
         LPAD(MONTH(@desde),2,'0') AS Mes,
        '002' AS numEstabRuc,
        ( SELECT SUM( IF(d.series LIKE 'F%', d.total_taxed , d.total_taxed*-1 )
        +  IF(d.series LIKE 'F%' ,d.total_unaffected, d.total_unaffected*-1)) AS total
        FROM documents AS d
        WHERE d.date_of_issue >=@desde AND date_of_issue <=@hasta
         ) AS  totalVentas, 'IVA' AS codigoOperativo
        FROM companies WHERE id = 1 ;

        END";
        DB::connection('tenant')->statement($sqlAts1);

        $sqlAts2 = "CREATE PROCEDURE `SP_COMPRAS_CABECERACOMPRAS`(
            IN `d` DATE,
            IN `h` DATE
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT 'Ats cabecera compras'
        BEGIN


        SET @desde = d ;
        SET @hasta =h ;
        SET @row_number = 0;

        SELECT
        CONCAT('C',(@row_number:=@row_number + 1)) AS  codCompra,
         p.codSustento,  CASE WHEN ps.identity_document_type_id = '6' THEN '01' ELSE
         (CASE WHEN ps.identity_document_type_id = '1' THEN '02' ELSE '03' END ) END AS tpIdProv,
          ps.number AS idProv , pd.DocumentTypeID as tipoComprobante,
        CASE WHEN ps.identity_document_type_id  IN ('4','7') THEN  '02' ELSE '' END AS tipoProv,
        CASE WHEN ps.identity_document_type_id  IN ('4','7') THEN  ps.name  ELSE '' END AS denoProv,
        ps.parteRel as parteRel, p.date_of_issue as  fechaRegistro,
        SUBSTRING(sequential_number,1,3) AS establecimiento ,SUBSTRING(sequential_number,4,3) AS puntoEmision,
        SUBSTRING(sequential_number,7,9) AS secuencial,p.date_of_issue AS fechaEmision, auth_number AS  autorizacion
        , 0 AS baseNoGraIva, p.total_unaffected AS baseImponible,  p.total_taxed AS baseImpGrav,0 AS baseImpExe,
        0 AS montoIce, p.total_igv AS montoIva , tr.valRetBien10, tr.valRetServ20, tr.valorRetBienes,
        tr.valRetServ50, tr.valorRetServicios, tr.valRetServ100, 0 AS totbasesImpReemb
        FROM purchases AS p LEFT JOIN persons AS ps ON p.supplier_id = ps.id
        LEFT JOIN cat_purchase_document_types2 AS pd ON p.document_type_intern = pd.idType
        LEFT JOIN (SELECT a.iddocumento, a.claveacceso, a.secuencial,a.numauthsustento,
        SUM(case when b.porcentajeRet = 10 then b.valorRet ELSE 0 END ) AS valRetBien10 ,
        SUM(case when b.porcentajeRet = 20 then b.valorRet ELSE 0 END ) AS valRetServ20 ,
        SUM(case when b.porcentajeRet = 30 then b.valorRet ELSE 0 END ) AS valorRetBienes ,
        SUM(case when b.porcentajeRet = 50 then b.valorRet ELSE 0 END ) AS valRetServ50 ,
        SUM(case when b.porcentajeRet = 70 then b.valorRet ELSE 0 END ) AS valorRetServicios ,
        SUM(case when b.porcentajeRet = 100 then b.valorRet ELSE 0 END ) AS valRetServ100
        FROM retenciones_join AS a
        INNER JOIN retentions_detail_ec AS b ON a.idRetencion = b.idRetencion
        INNER JOIN cat_retention_types AS c ON b.codretencion = c.code
        WHERE c.type_id = '02'
        GROUP BY a.iddocumento, a.claveacceso, a.secuencial,a.numauthsustento) AS tr ON  p.id = tr.iddocumento
        WHERE date_of_issue >=@desde
        AND date_of_issue <=@hasta
        AND pd.DocumentTypeID <> '376'
        ORDER BY p.id asc
        ;
        END";
        DB::connection('tenant')->statement($sqlAts2);

        $sqlAts3="CREATE PROCEDURE `SP_COMPRAS_CABECERACOMPRAS_pagoExterior`(
            IN `d` DATE,
            IN `h` DATE,
            IN `c` VARCHAR(50)
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT 'pagoExterior de cavbercera compras'
        BEGIN


        SET @desde = d ;
        SET @hasta = h ;
        SET @compra = c ;
        SET @row_number = 0;

        SELECT pagoLocExt , tipoRegi, paisEfecPagoGen, paisEfecPagoParFis, denopagoRegFis, paisEfecPago ,
        aplicConvDobTrib, pagExtSujRetNorLeg
        FROM (
        SELECT CONCAT('C',(@row_number:=@row_number + 1)) as id,
        IF(pr.pagoLocExt = 'Exterior', '02' , '01') AS pagoLocExt,
        IF(pr.pagoLocExt = 'Exterior', '01' , '') AS tipoRegi,
        IF(pr.pagoLocExt = 'Exterior', pr.country_id , '') AS paisEfecPagoGen,
        '' AS paisEfecPagoParFis,
        '' AS denopagoRegFis,
        IF(pr.pagoLocExt = 'Exterior', pr.country_id , '')  AS paisEfecPago,
        '' AS aplicConvDobTrib, '' AS pagExtSujRetNorLeg
        FROM purchases AS p LEFT JOIN cat_purchase_document_types2 AS pd
        ON p.document_type_intern = pd.idType
        LEFT JOIN persons AS pr ON p.supplier_id = pr.id
        WHERE date_of_issue >=@desde
        AND date_of_issue <=@hasta
        AND pd.DocumentTypeID <> '376'
        ORDER BY p.id ASC
        )  AS aa
        WHERE aa.id COLLATE UTF8MB4_GENERAL_CI = @compra
        ;
        END";
        DB::connection('tenant')->statement($sqlAts3);

        $sqlAts4 = "CREATE PROCEDURE `SP_COMPRAS_formasDePago`(
            IN `d` DATE,
            IN `h` DATE
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT 'formas de pago de compras'
        BEGIN
        SET @desde = d ;
        SET @hasta = h ;

        SET @row_number = 0;

        SELECT aa.codCompra AS codModulo , bb.pago_sri AS forma
        FROM (
        SELECT p.id ,CONCAT('C',(@row_number:=@row_number + 1))  AS codCompra
        FROM purchases AS p
        LEFT JOIN cat_purchase_document_types2 AS pd ON p.document_type_intern = pd.idType
        WHERE p.date_of_issue >= @desde
        AND  p.date_of_issue <= @hasta
        AND pd.DocumentTypeID <> '376'
        ORDER BY p.id ASC
        ) AS aa
        INNER JOIN
        (
        SELECT d.id ,IF(p2.pago_sri IS NULL , p1.pago_sri , p2.pago_sri) AS pago_sri
        FROM  purchases AS d
        INNER JOIN persons AS a ON d.supplier_id = a.id
        left JOIN purchase_fee AS df ON d.id = df.purchase_id
        LEFT  JOIN  purchase_payments AS dp ON df.purchase_id = dp.purchase_id
        AND df.number = dp.fee_id
        LEFT JOIN payment_method_types AS p1 ON df.payment_method_type_id = p1.id
        LEFT JOIN payment_method_types AS p2 ON dp.payment_method_type_id = p2.id
        LEFT JOIN cat_purchase_document_types2 AS pd ON d.document_type_intern = pd.idType
        WHERE pd.DocumentTypeID <> '376' AND
        d.date_of_issue >= @desde
        AND  d.date_of_issue <= @hasta
        ) AS bb ON aa.id = bb.id
        ;


        END";
        DB::connection('tenant')->statement($sqlAts4);

        $sqlAts5 = "CREATE PROCEDURE `SP_COMPRAS_retencionesCompras`(
            IN `d` DATE,
            IN `h` DATE
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN


        SET @desde = d ;
        SET @hasta = h ;

        SET @row_number = 0;
        SELECT codCompra ,  codRetAir, baseImpAir, porcentajeAir, valRetAir,'' AS fechaPagoDiv,
        0 AS imRentaSoc , '' AS anioUtDiv
        FROM (
        SELECT p.id ,CONCAT('C',(@row_number:=@row_number + 1))  AS codCompra
        FROM purchases AS p
        LEFT JOIN cat_purchase_document_types2 AS pd ON p.document_type_intern = pd.idType
        WHERE p.date_of_issue >= @desde
        AND  p.date_of_issue <= @hasta
        AND pd.DocumentTypeID <> '376'
        ORDER BY p.id ASC
        ) AS aa
        INNER JOIN
        (
        SELECT a.idDocumento, b.codRetencion AS codRetAir , b.baseret AS baseImpAir,
        b.porcentajeret AS porcentajeAir , b.valorRet AS valRetAir
        FROM retenciones_join AS a INNER JOIN retentions_detail_ec AS b ON
        a.idretencion = b.idretencion
        INNER JOIN cat_retention_types AS c ON b.codretencion = c.code
        WHERE c.type_id = '01'
        ) AS bb ON aa.id = bb.idDocumento
        ;


        END";
        DB::connection('tenant')->statement($sqlAts5);

        $sqlAts6 = "CREATE PROCEDURE `SP_COMPRAS_reembolsos`(
            IN `d` DATE,
            IN `h` DATE
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN


        SET @desde = d ;
        SET @hasta = h ;

        SELECT p.id, CONCAT('C',p.id ) AS codCompra,'03' AS tipoComprobanteReemb, '03' AS tpIdProvReemb,
        '12344' AS idProvReemb, '001' AS establecimientoReemb, '001' AS puntoEmisionReemb, 6 AS secuencialReemb,
        '2024-03-01' AS fechaEmisionReemb, '2024-03-01' AS autorizacionReemb, 0 AS baseImponibleReemb,
        500 AS baseImpGravReemb, 0 AS baseNoGraIvaReemb, 0 AS baseImpExeReemb, 0 AS montoIceRemb, 60 AS montoIvaRemb
        FROM purchases AS p
        WHERE p.date_of_issue >= @desde
        AND  p.date_of_issue <= @hasta
        ;

        END";
        DB::connection('tenant')->statement($sqlAts6);

        $sqlAts7 = "CREATE PROCEDURE `SP_VENTAS_detalleVentas`(
            IN `d` DATE,
            IN `h` DATE
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN
        SET @desde = d ;
        SET @hasta = h ;

        SET @row_number = 0;


        SELECT
        CONCAT('V',(@row_number:=@row_number + 1)) AS codVenta  ,
        bb.*
        FROM (
        SELECT
        tpIdCliente, idCliente, parteRelVtas, tipoCliente, denoCli, tipoComprobante,
        tipoEmision, COUNT(*) AS numeroComprobantes, SUM(baseNoGraIva) AS baseNoGraIva, SUM(baseImponible) AS baseImponible,
        SUM(baseImpGrav) AS baseImpGrav, SUM(montoIva) AS montoIva, SUM(montoIce) AS montoIce,
        SUM(valorRetIva) AS valorRetIva, SUM(valorRetRenta) AS valorRetRenta
        FROM (
        SELECT d.id, CONCAT('V',d.id) AS codVenta,
        codeSri AS tpIdCliente, pr.number AS idCliente,
        CASE WHEN pr.parteRel IS NULL THEN 'NO' ELSE pr.parteRel END AS parteRelVtas,
        CASE WHEN codeSri = '06' THEN '01' ELSE '' END AS tipoCliente,
        CASE WHEN codeSri = '06' THEN pr.name ELSE '' END AS denoCli  ,
        CASE WHEN d.Series LIKE 'F%' THEN '01'ELSE '04' END AS tipoComprobante,
        'E' AS tipoEmision, 0 AS numeroComprobantes,
         0 AS baseNoGraIva, d.total_unaffected AS baseImponible, d.total_taxed AS baseImpGrav,
        d.total_igv AS montoIva, 0 AS  montoIce,
        r.retiva AS valorRetIva, r.retrenta AS valorRetRenta
        FROM documents AS d LEFT JOIN persons AS pr ON d.customer_id = pr.id
        LEFT JOIN cat_identity_document_types AS ci ON pr.identity_document_type_id = ci.id
        LEFT JOIN (SELECT a.document_id,
        SUM(case when c.account_movement_id = 441 THEN c.debe ELSE 0 END) AS retrenta,
        SUM(case when c.account_movement_id = 440 THEN c.debe ELSE 0 END) AS retiva
        FROM document_payments AS a
        INNER JOIN accounting_entries AS b ON CONCAT('CF',a.id) = b.document_id
        INNER JOIN accounting_entry_items AS c ON b.id = c.accounting_entrie_id
        WHERE a.payment_method_type_id = '99'
        -- AND accounting_entrie_id = 3788
        GROUP BY a.document_id) AS r ON d.id = r.document_id
        WHERE d.date_of_issue >= @desde
        AND  d.date_of_issue <= @hasta
        ) AS aa
        GROUP BY tpIdCliente, idCliente, parteRelVtas, tipoCliente, denoCli, tipoComprobante, tipoEmision
        ORDER BY idCliente ASC, tipoComprobante ASC
        ) AS bb

        ;
        END";
        DB::connection('tenant')->statement($sqlAts7);

        $sqlAts8 = "CREATE PROCEDURE `SP_VENTAS_formasDePago`(
            IN `d` DATE,
            IN `h` DATE
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN


        SET @desde = d ;
        SET @hasta = h ;

        SET @row_number = 0;

        SELECT codVenta AS codModulo, pago_sri AS forma
        FROM (
        SELECT
        CONCAT('V',(@row_number:=@row_number + 1)) AS codVenta  ,
        bb.*
        FROM (SELECT idCliente, tipoComprobante
        FROM (SELECT
        pr.number AS idCliente,CASE WHEN d.Series LIKE 'F%' THEN '01'ELSE '04' END AS tipoComprobante
        FROM documents AS d LEFT JOIN persons AS pr ON d.customer_id = pr.id
        LEFT JOIN cat_identity_document_types AS ci ON pr.identity_document_type_id = ci.id
        WHERE d.date_of_issue >= @desde
        AND  d.date_of_issue <= @hasta
        ) AS aa GROUP BY idCliente, tipoComprobante ORDER BY idCliente ASC, tipoComprobante ASC
        ) AS bb ) AS cc
        LEFT JOIN
        (SELECT *
        FROM (
        SELECT a.number,IF(p2.pago_sri IS NULL , p1.pago_sri , p2.pago_sri) AS pago_sri
        FROM  documents AS d
        INNER JOIN persons AS a ON d.customer_id = a.id
        left JOIN document_fee AS df ON d.id = df.document_id
        LEFT  JOIN  document_payments AS dp ON df.document_id = dp.document_id
        AND df.number = dp.fee_id
        LEFT JOIN payment_method_types AS p1 ON df.payment_method_type_id = p1.id
        LEFT JOIN payment_method_types AS p2 ON dp.payment_method_type_id = p2.id
        WHERE d.series LIKE 'F%' AND
        d.date_of_issue >= @desde
        AND  d.date_of_issue <= @hasta
        ) AS cc
        GROUP BY cc.number, cc.pago_sri
        ) AS dd ON cc.idCliente = dd.number
        WHERE cc.tipoComprobante = '01'
        ;

        END";
        DB::connection('tenant')->statement($sqlAts8);

        $sqlAts9 = "CREATE PROCEDURE `SP_VENTAS_ventasEstablecimiento`(
            IN `d` DATE,
            IN `h` DATE
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN


        SET @desde = d ;
        SET @hasta = h ;

        SELECT e.code AS codEstab , SUM( d.total_taxed + d.total_unaffected ) AS ventasEstab
        FROM documents AS d INNER JOIN establishments AS e ON d.establishment_id = e.id
        WHERE d.date_of_issue >= @desde
        AND  d.date_of_issue <= @hasta
        GROUP BY  e.code
        ;
        END";
        DB::connection('tenant')->statement($sqlAts9);


        $sqlAts10 = "CREATE PROCEDURE `SP_ANULADOS`(
            IN `d` DATE,
            IN `h` DATE
        )
        LANGUAGE SQL
        NOT DETERMINISTIC
        CONTAINS SQL
        SQL SECURITY DEFINER
        COMMENT ''
        BEGIN
        SET @desde = d ;
        SET @hasta = h ;

        SELECT '01' AS tipoComprobante, '001' AS establecimiento, '001' AS puntoEmision,
        '25' AS secuencialInicio, '25' AS secuencialFin, '123456789' AS autorizacion
        ;
        END";
        DB::connection('tenant')->statement($sqlAts10);

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

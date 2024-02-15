<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Importaciones</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<div>
    <h3 align="center" class="title"><strong>Reporte liquidación de importación</strong></h3>
</div>
<br>
@if(!empty($records))
    @php
        $totalUnidades = 0;
        $totalEuros = 0;
        $totalFOB = 0;
        $totalUnitarioFOB = 0;
        $totalGastosHastaFOB = 0;
        $totalUnitarioFOB1 = 0;
        $totalFOBFinal = 0;
        $totalEmpaque = 0;
        $totalFlete = 0;
        $totalSeguro = 0;
        $totalCIF = 0;
        $totalADVALOREN = 0;
        $totalFODINFA = 0;
        $totalICE = 0;
        $totalIVA = 0;
        $totalImpuestosTributos = 0;
        $totalUnitarioIntereses = 0;
        $totalIntereses = 0;
        $totalGastosLocales = 0;
        $totalISD = 0;
        $totalComunicaciones = 0;
        $totalGastos = 0;
        $totalFactorImportacion = 0;
        $totalUnitario = 0;
        $totalTotal = 0;
        
        foreach($records as $record) {
            $totalUnidades += $record->unidadestoal;
            $totalEuros += $record->Eur;
            $totalUnitarioFOB += $record->fob;
            $totalFOB += $record->fobtotal;
            $totalGastosHastaFOB += $record->gastohastafob;
            $totalUnitarioFOB1 += $record->fob_finalunit;
            $totalFOBFinal += $record->fob_finaltotal;
            $totalEmpaque += $record->empaque;
            $totalFlete += $record->fletetotal;
            $totalSeguro += $record->segurototal;
            $totalCIF += $record->cif;
            $totalADVALOREN += $record->valoradvaloren;
            $totalFODINFA += $record->fodinfa;
            $totalICE += $record->Ice;
            $totalIVA += $record->iva;
            $totalImpuestosTributos += $record->total_impuestos_tributos;
            $totalUnitarioIntereses += $record->interes;
            $totalIntereses += $record->interestotal;
            $totalGastosLocales += $record->gastos;
            $totalISD += $record->isd;
            $totalComunicaciones += $record->comunicaciones;
            $totalGastos += $record->totalgastos;
            $totalFactorImportacion += $record->FACTOR; 
            $totalUnitario += $record->costo_unitario;
            $totalTotal += $record->costototal;
        }
    @endphp

    <div>
        <div>
            <table>
                <thead>
                    <tr>
                        <th rowspan="2">Serie</th>
                        <th rowspan="2">Número</th>
                        <th rowspan="2">Importacion</th>
                        <th rowspan="2">Numero línea</th>
                        <th rowspan="2">Código artículo</th>
                        <th rowspan="2">Referencia</th>
                        <th rowspan="2">Descripción</th>
                        <th rowspan="2">Partida arancelaria</th>
                        <th rowspan="2">Porcentaje advaloren</th>
                        <th rowspan="2">Porcentaje fodinfa</th>
                        <th rowspan="2">Unidades total</th>
                        <th rowspan="2">Euros</th>
                        <th colspan="2">FOB FACTURA</th>
                        <th rowspan="2">Gasto hasta FOB</th>
                        <th colspan="2">FOB FINAL</th>
                        <th rowspan="2">Empaque</th>
                        <th rowspan="2">Flete</th>
                        <th rowspan="2">Seguro</th>
                        <th rowspan="2">TOTAL CIF</th>
                        <th rowspan="2">ADVALOREN</th>
                        <th rowspan="2">FODINFA</th>
                        <th rowspan="2">ICE</th>
                        <th rowspan="2">IVA</th>
                        <th rowspan="2">TOTAL IMPUESTOS Y TRIBUTOS</th>
                        <th colspan="2">Intereses</th>
                        <th rowspan="2">Gastos locales</th>
                        <th rowspan="2">Impuesto Salida Divisas</th>
                        <th rowspan="2">Comunicaciones</th>
                        <th rowspan="2">Total Gastos</th>
                        <th rowspan="2">Factor Importacion</th>
                        <th rowspan="2">Unitario</th>
                        <th rowspan="2">Total</th>
                    </tr>
                    <tr>
                        <th>Unitario</th>
                        <th>Total</th>
                        <th>Unitario</th>
                        <th>Total</th>
                        <th>Unitario</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $key => $value)
                    <tr>
                        <td>{{$value->series}}</td>
                        <td>{{$value->number}}</td>
                        <td>{{$value->importacion}}</td>
                        <td>{{$value->Numerolinea}}</td>
                        <td>{{$value->codarticulo}}</td>
                        <td>{{$value->referencia }}</td>
                        <td>{{$value->descripcion }}</td>
                        <td>{{$value->partidaarancelaria }}</td>
                        <td>{{$value->porcentaje }}</td>
                        <td>{{$value->porcentajef }}</td>
                        <td>{{$value->unidadestoal }}</td>
                        <td>{{$value->Eur }}</td>
                        <td>{{$value->fob }}</td>
                        <td>{{$value->fobtotal }}</td>
                        <td>{{$value->gastohastafob }}</td>
                        <td>{{$value->fob_finalunit }}</td>
                        <td>{{$value->fob_finaltotal }}</td>
                        <td>{{$value->empaque }}</td>
                        <td>{{$value->fletetotal }}</td>
                        <td>{{$value->segurototal }}</td>
                        <td>{{$value->cif }}</td>
                        <td>{{$value->valoradvaloren }}</td>
                        <td>{{$value->fodinfa }}</td>
                        <td>{{$value->Ice }}</td>
                        <td>{{$value->iva }}</td>
                        <td>{{$value->total_impuestos_tributos }}</td>
                        <td>{{$value->interes }}</td>
                        <td>{{$value->interestotal }}</td>
                        <td>{{$value->gastos }}</td>
                        <td>{{$value->isd }}</td>
                        <td>{{$value->comunicaciones }}</td>
                        <td>{{$value->totalgastos }}</td>
                        <td>{{$value->FACTOR }}</td>
                        <td>{{$value->costo_unitario }}</td>
                        <td>{{$value->costototal }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="10"><strong>Totales</strong></td>
                        <td><strong>{{$totalUnidades}}</strong></td>
                        <td><strong>{{$totalEuros}}</strong></td>
                        <td><strong>{{$totalUnitarioFOB}}</strong></td>
                        <td><strong>{{$totalFOB}}</strong></td>
                        <td><strong>{{$totalGastosHastaFOB}}</strong></td>
                        <td><strong>{{$totalUnitarioFOB1}}</strong></td>
                        <td><strong>{{$totalFOBFinal}}</strong></td>
                        <td><strong>{{$totalEmpaque}}</strong></td>
                        <td><strong>{{$totalFlete}}</strong></td>
                        <td><strong>{{$totalSeguro}}</strong></td>
                        <td><strong>{{$totalCIF}}</strong></td>
                        <td><strong>{{$totalADVALOREN}}</strong></td>
                        <td><strong>{{$totalFODINFA}}</strong></td>
                        <td><strong>{{$totalICE}}</strong></td>
                        <td><strong>{{$totalIVA}}</strong></td>
                        <td><strong>{{$totalImpuestosTributos}}</strong></td>
                        <td><strong>{{$totalUnitarioIntereses}}</strong></td>
                        <td><strong>{{$totalIntereses}}</strong></td>
                        <td><strong>{{$totalGastosLocales}}</strong></td>
                        <td><strong>{{$totalISD}}</strong></td>
                        <td><strong>{{$totalComunicaciones}}</strong></td>
                        <td><strong>{{$totalGastos}}</strong></td>
                        <td><strong>{{$totalFactorImportacion}}</strong></td>
                        <td><strong>{{$totalUnitario}}</strong></td>
                        <td><strong>{{$totalTotal}}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@else
    <div>
        <p>No se encontraron registros.</p>
    </div>
@endif
</body>
</html>

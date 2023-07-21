<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Importaciones</title>
    </head>
    <body>
        <div>
            <h3 align="center" class="title"><strong>Reporte liquidación de importacion</strong></h3>

        </div>
        <br>
        @if(!empty($records))
            <div class="">
                <div class=" ">
                    <table class="">
                        <thead>
                            <tr style="align-content: center; text-align: center; align-items: center">
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
                                <th rowspan="1" colspan="2">FOB FACTURA</th>
                                <th rowspan="2">Gasto hasta FOB</th>
                                <th rowspan="1" colspan="2">FOB FINAL</th>
                                <th rowspan="2">Flete</th>
                                <th rowspan="2">Seguro</th>
                                <th rowspan="2">TOTAL CIF</th>
                                <th rowspan="2">ADVALOREN</th>
                                <th rowspan="2">FODINFA</th>
                                <th rowspan="2">ICE</th>
                                <th rowspan="2">IVA</th>
                                <th rowspan="2">TOTAL IMPUESTOS Y TRIBUTOS</th>
                                <th rowspan="1" colspan="2">Intereses</th>
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
                                <td class="celda">{{$value->series}}</td>
                                <td class="celda">{{$value->number}}</td>
                                <td class="celda">{{$value->importacion}}</td>
                                <td class="celda">{{$value->Numerolinea}}</td>
                                <td class="celda">{{$value->codarticulo}}</td>
                                <td class="celda">{{$value->referencia }}</td>
                                <td class="celda">{{$value->descripcion }}</td>
                                <td class="celda">{{$value->partidaarancelaria }}</td>
                                <td class="celda">{{$value->porcentaje }}</td>
                                <td class="celda">{{$value->porcentajef }}</td>
                                <td class="celda">{{$value->unidadestoal }}</td>
                                <td class="celda">{{$value->fob }}</td>
                                <td class="celda">{{$value->fobtotal }}</td>
                                <td class="celda">{{$value->gastohastafob }}</td>
                                <td class="celda">{{$value->fob_finalunit }}</td>
                                <td class="celda">{{$value->fob_finaltotal }}</td>
                                <td class="celda">{{$value->fletetotal }}</td>
                                <td class="celda">{{$value->segurototal }}</td>
                                <td class="celda">{{$value->cif }}</td>
                                <td class="celda">{{$value->valoradvaloren }}</td>
                                <td class="celda">{{$value->fodinfa }}</td>
                                <td class="celda">{{$value->Ice }}</td>
                                <td class="celda">{{$value->iva }}</td>
                                <td class="celda">{{$value->total_impuestos_tributos }}</td>
                                <td class="celda">{{$value->interes }}</td>
                                <td class="celda">{{$value->interestotal }}</td>
                                <td class="celda">{{$value->gastos }}</td>
                                <td class="celda">{{$value->isd }}</td>
                                <td class="celda">{{$value->comunicaciones }}</td>
                                <td class="celda">{{$value->totalgastos }}</td>
                                <td class="celda">{{$value->FACTOR }}</td>
                                <td class="celda">{{$value->costo_unitario }}</td>
                                <td class="celda">{{$value->costototal }}</td>

                            </tr>
                            @endforeach
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

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
                            <tr>
                                <th>Serie</th>
                                <th>Número</th>
                                <th>Importacion</th>
                                <th>Numero línea</th>
                                <th>Código artículo</th>
                                <th>Referencia</th>
                                <th>Descripción</th>
                                <th>Partida arancelaria</th>
                                <th>Porcentaje advaloren</th>
                                <th>Porcentaje fodinfa</th>
                                <th>Unidades total</th>
                                <th>FOB</th>
                                <th>Gasto hasta FOB</th>
                                <th>Nuevo FOB</th>
                                <th>FOB total</th>
                                <th>Flete</th>
                                <th>Flete total</th>
                                <th>Seguro</th>
                                <th>Seguro total</th>
                                <th>CIF</th>
                                <th>Valor ADVALOREN</th>
                                <th>FODINFA</th>
                                <th>ICE</th>
                                <th>IVA</th>
                                <th>Gastos</th>
                                <th>Gastos Total</th>
                                <th>Costo</th>
                                <th>Interes</th>
                                <th>Interes total</th>
                                <th>Costo calculado</th>
                                <th>Total linea</th>
                                <th>Factor</th>

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
                                <td class="celda">{{$value->gastohastafob }}</td>
                                <td class="celda">{{$value->nuevofob }}</td>
                                <td class="celda">{{$value->fobTotal }}</td>
                                <td class="celda">{{$value->flete }}</td>
                                <td class="celda">{{$value->fletetotal }}</td>
                                <td class="celda">{{$value->seguro }}</td>
                                <td class="celda">{{$value->segurototal }}</td>
                                <td class="celda">{{$value->cif }}</td>
                                <td class="celda">{{$value->valoradvaloren }}</td>
                                <td class="celda">{{$value->fodinfa }}</td>
                                <td class="celda">{{$value->Ice }}</td>
                                <td class="celda">{{$value->iva }}</td>
                                <td class="celda">{{$value->gastos }}</td>
                                <td class="celda">{{$value->gastostotal }}</td>
                                <td class="celda">{{$value->costo }}</td>
                                <td class="celda">{{$value->interes }}</td>
                                <td class="celda">{{$value->interestotal }}</td>
                                <td class="celda">{{$value->costocalculado }}</td>
                                <td class="celda">{{$value->totallinea }}</td>
                                <td class="celda">{{$value->factor }}</td>

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

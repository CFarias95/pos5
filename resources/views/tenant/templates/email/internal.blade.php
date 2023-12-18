<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <title>Pedido Interno</title>
    <style>
        body {
            color: #000;
        }
        ul {
            list-style: none;
        }
    </style>
</head>
<body>
    @if($estado != 'Created')
    <p>Estimad@: {{ $name }}<br>
        Le informamos que el pedido interno IR- {{ $id }} a sido {{ $estado }}</p>
        @if($body)
        <div style="border: 1px solid black;">{{$body}}</div><br>
        @endif
    @else
    <p>Estimad@: {{ $name }}<br>

        Le informamos que se a generado el pedido interno IR- {{ $id }}</p>
        @if($body)
        <div style="border: 1px solid black;">{{$body}}</div><br>
        @endif
    @endif
</body>
</html>

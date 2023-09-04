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

    @if($estado)

    <p>Estimad@: {{ $name }}<br>

        Le informamos que el pedido interno IR- {{ $id }} a sido {{ $estado }}</p>

    @else

    <p>Estimad@: {{ $name }}<br>

        Le informamos que se a generado el pedido interno IR- {{ $id }}</p>

    @endif

</body>
</html>

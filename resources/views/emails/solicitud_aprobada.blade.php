<html>
<head>
    <title>Solicitud Aprobada</title>
</head>
<body>
    <h1>Solicitud Aprobada</h1>
    <p>Hola,</p>
    <p>Tu solicitud con ID <strong>{{ $solicitud['id'] }}</strong> ha sido aprobada.</p>
    <p>Aprobador: {{ $aprobador }}</p>
    <p>Detalles:</p>
    <ul>
        <li>Fecha: {{ $solicitud['fecha'] }}</li>
        <li>Descripción: {{ $solicitud['descripcion'] }}</li>
    </ul>
    <p>Gracias,</p>
    <p>El equipo de Horas Extras</p>
</body>
</html>

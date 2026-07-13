<html>
<head>
    <title>Solicitud Rechazada</title>
</head>
<body>
    <h1>Solicitud Rechazada</h1>
    <p>Hola,</p>
    <p>Lamentamos informarte que tu solicitud con ID <strong>{{ $solicitud['id'] }}</strong> ha sido rechazada.</p>
    <p>Rechazador: {{ $rechazador }}</p>
    <p>Detalles:</p>
    <ul>
        <li>Fecha: {{ $solicitud['fecha'] }}</li>
        <li>Descripción: {{ $solicitud['descripcion'] }}</li>
    </ul>
    <p>Gracias,</p>
    <p>El equipo de Horas Extras</p>
</body>
</html>

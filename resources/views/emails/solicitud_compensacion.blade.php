<html>
<head>
    <title>Estado de Compensación</title>
</head>
<body>
    <h1>Estado de Compensación</h1>
    <p>Hola,</p>
    <p>El estado de tu solicitud con ID <strong>{{ $solicitud['id'] }}</strong> ha cambiado a: <strong>{{ $estado }}</strong>.</p>
    <p>Detalles:</p>
    <ul>
        <li>Fecha: {{ $solicitud['fecha'] }}</li>
        <li>Descripción: {{ $solicitud['descripcion'] }}</li>
    </ul>
    <p>Gracias,</p>
    <p>El equipo de Horas Extras</p>
</body>
</html>

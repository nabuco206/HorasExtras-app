<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de Solicitudes Pendientes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Notificación de Solicitudes Pendientes</h2>
        </div>
        <p>Estimado/a {{ $jefatura->nombre }},</p>
        <p>Se le notifica que tiene las siguientes solicitudes pendientes:</p>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($detalleSolicitudes as $detalle)
                <tr>
                    <td>{{ $detalle['ID'] }}</td>
                    <td>{{ $detalle['Usuario'] }}</td>
                    <td>{{ $detalle['Fecha'] }}</td>
                    <td>{{ $detalle['Estado'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <p>Por favor, revise estas solicitudes a la brevedad.</p>
        <p>Atentamente,<br>El equipo de Horas Extras</p>
    </div>
</body>
</html>

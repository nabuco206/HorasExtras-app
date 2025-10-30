<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== VERIFICADOR DEL SISTEMA HE ===\n\n";

use App\Models\TblSolicitudHe;
use Illuminate\Support\Facades\DB;

// 1. Verificar usuarios faltantes en tbl_personas
echo "1. Verificando usuarios en solicitudes HE...\n";
$usuariosHE = TblSolicitudHe::select('username')->distinct()->pluck('username');
$usuariosFaltantes = [];

foreach ($usuariosHE as $username) {
    $existe = DB::table('tbl_personas')->where('username', $username)->exists();
    if (!$existe) {
        $usuariosFaltantes[] = $username;
    }
}

if (count($usuariosFaltantes) > 0) {
    echo "   ⚠️  Usuarios faltantes en tbl_personas: " . count($usuariosFaltantes) . "\n";
    foreach ($usuariosFaltantes as $user) {
        echo "   - {$user}\n";

        // Crear usuario automáticamente
        try {
            DB::table('tbl_personas')->insert([
                'username' => $user,
                'nombre' => 'Usuario Auto-generado',
                'apellido' => $user,
                'password' => bcrypt('temp_password_' . $user),
                'cod_fiscalia' => 1,
                'flag_lider' => 0,
                'flag_activo' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            echo "     ✅ Usuario creado automáticamente\n";
        } catch (\Exception $e) {
            echo "     ❌ Error creando usuario: " . $e->getMessage() . "\n";
        }
    }
} else {
    echo "   ✅ Todos los usuarios de HE existen en tbl_personas\n";
}

// 2. Verificar estados
echo "\n2. Verificando estados del sistema...\n";
$estadosRequeridos = [1, 3, 4, 5, 6]; // INGRESADO, APROBADO_JEFE, RECHAZADO_JEFE, etc.
$estadosFaltantes = [];

foreach ($estadosRequeridos as $estadoId) {
    $existe = DB::table('tbl_estados')->where('id', $estadoId)->exists();
    if (!$existe) {
        $estadosFaltantes[] = $estadoId;
    }
}

if (count($estadosFaltantes) > 0) {
    echo "   ⚠️  Estados faltantes: " . implode(', ', $estadosFaltantes) . "\n";
} else {
    echo "   ✅ Todos los estados requeridos existen\n";
}

// 3. Verificar HE sin seguimiento
echo "\n3. Verificando HE sin seguimiento...\n";
$heSinSeguimiento = TblSolicitudHe::whereNotExists(function($query) {
    $query->select(DB::raw(1))
          ->from('tbl_seguimiento_solicituds')
          ->whereRaw('tbl_seguimiento_solicituds.id_solicitud_he = tbl_solicitud_hes.id');
})->count();

if ($heSinSeguimiento > 0) {
    echo "   ⚠️  HE sin seguimiento: {$heSinSeguimiento}\n";
} else {
    echo "   ✅ Todas las HE tienen seguimiento\n";
}

// 4. Estadísticas generales
echo "\n4. Estadísticas del sistema:\n";
$totalHE = TblSolicitudHe::count();
$hePendientes = TblSolicitudHe::where('id_estado', 1)->count();
$heAprobadas = TblSolicitudHe::where('id_estado', 3)->count();
$totalPersonas = DB::table('tbl_personas')->count();

echo "   - Total HE: {$totalHE}\n";
echo "   - HE Pendientes: {$hePendientes}\n";
echo "   - HE Aprobadas: {$heAprobadas}\n";
echo "   - Total Personas: {$totalPersonas}\n";

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";

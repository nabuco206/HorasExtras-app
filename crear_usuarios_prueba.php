<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CREANDO USUARIOS EN TBL_PERSONAS ===\n\n";

use Illuminate\Support\Facades\DB;

// Crear usuarios en tbl_personas para los usuarios de prueba
$usuarios = ['SISTEMA', 'usuario_test_1', 'usuario_test_2', 'usuario_test_3'];

foreach ($usuarios as $username) {
    $existe = DB::table('tbl_personas')->where('username', $username)->exists();
    if (!$existe) {
        DB::table('tbl_personas')->insert([
            'username' => $username,
            'nombre' => 'Usuario de Prueba',
            'apellido' => $username,
            'password' => bcrypt('password'),
            'cod_fiscalia' => 1,
            'flag_lider' => 1,
            'flag_activo' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "Usuario creado: {$username}\n";
    } else {
        echo "Usuario ya existe: {$username}\n";
    }
}

echo "\n=== USUARIOS CREADOS EXITOSAMENTE ===\n";

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use App\Models\TblPersona; // Asegúrate de importar el modelo correcto

class AuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $username = $credentials['username'];
        $password = $credentials['password'];


        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'username' => ['Las credenciales proporcionadas no coinciden con nuestros registros.'],
        ]);

        // Configuración del servidor LDAP
        // $ldapHost = env('LDAP_HOST', '172.18.1.7');
        // $ldapDomain = env('LDAP_DOMAIN', 'minpublico.cl');
        // $ldapDn = env('LDAP_BASE_DN', 'dc=minpublico,dc=cl');

        // // Conexión al servidor LDAP
        // $ldapConnection = @ldap_connect($ldapHost);
        // ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
        // ldap_set_option($ldapConnection, LDAP_OPT_REFERRALS, 0);

        // if (!$ldapConnection) {
        //     throw ValidationException::withMessages([
        //         'username' => ['No se pudo conectar al servidor LDAP.'],
        //     ]);
        // }

        // // Intentar autenticación con el servidor LDAP
        // $ldapBind = @ldap_bind($ldapConnection, $username . "@" . $ldapDomain, $password);

        // if (!$ldapBind) {
        //     throw ValidationException::withMessages([
        //         'username' => ['Credenciales inválidas.'],
        //     ]);
        // }

        // // Si la autenticación es exitosa, buscar el usuario en la tabla tbl_personas
        // $persona = TblPersona::where('username', $username)->first();

        // if (!$persona) {
        //     throw ValidationException::withMessages([
        //         'username' => ['El usuario no está registrado en el sistema.'],
        //     ]);
        // }

        // // Crear sesión para el usuario
        // $request->session()->regenerate();

        // // Autenticar al usuario manualmente
        // Auth::login($persona);

        // return redirect()->intended(route('dashboard'));
    }
}

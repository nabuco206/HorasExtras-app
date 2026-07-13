<?php

namespace App\Services;

use App\Models\TblPersona;
use Illuminate\Support\Facades\Log;

class UserService
{
    /**
     * Obtener los líderes de un usuario por su username.
     *
     * @param string $username
     * @return \Illuminate\Support\Collection
     */
    public function getLideresByUsername(string $username)
    {
        $persona = TblPersona::where('username', $username)->first();

        if (!$persona) {
            return collect(); // Retorna una colección vacía si no se encuentra el usuario
        }

        // Buscar líderes en tbl_persona con el mismo cod_fiscalia y id_rol = 2
        $lideresPersona = TblPersona::where('cod_fiscalia', $persona->cod_fiscalia)
            ->where('id_rol', 2)
            ->where('flag_activo', 1)
            ->get();

        // Buscar líderes en tbl_liders con el mismo cod_fiscalia
        // $lideresTblLiders = \DB::table('tbl_liders')
        //     ->join('tbl_personas', 'tbl_liders.persona_id', '=', 'tbl_personas.id')
        //     ->where('tbl_liders.cod_fiscalia', $persona->cod_fiscalia)
        //     ->where('tbl_liders.flag_activo', 1)
        //     ->select('tbl_personas.*')
        //     ->get();
        $lideresTblLiders = \App\Models\TblPersona::join('tbl_liders', 'tbl_personas.id', '=', 'tbl_liders.persona_id')
            ->where('tbl_liders.cod_fiscalia', $persona->cod_fiscalia)
            ->where('tbl_liders.flag_activo', 1)
            ->select('tbl_personas.*')
            ->get();
        Log::info('Liders: ' . $lideresTblLiders);

        return $lideresPersona->merge($lideresTblLiders);
    }

    /**
     * Obtener los líderes de un usuario por su ID.
     *
     * @param int $id
     * @return \Illuminate\Support\Collection
     */
    public function getLideresById(int $id)
    {
        $persona = TblPersona::find($id);

        if (!$persona) {
            return collect(); // Retorna una colección vacía si no se encuentra el usuario
        }

        // Buscar líderes en tbl_persona con el mismo cod_fiscalia y id_rol = 2
        $lideresPersona = TblPersona::where('cod_fiscalia', $persona->cod_fiscalia)
            ->where('id_rol', 2)
            ->get();

        // Buscar líderes en tbl_liders con el mismo cod_fiscalia
        $lideresTblLiders = \DB::table('tbl_liders')
            ->join('tbl_persona', 'tbl_liders.persona_id', '=', 'tbl_persona.id')
            ->where('tbl_liders.cod_fiscalia', $persona->cod_fiscalia)
            ->select('tbl_persona.*') // Selecciona todos los campos de tbl_persona
            ->get();

        return $lideresPersona->merge($lideresTblLiders);
    }
}

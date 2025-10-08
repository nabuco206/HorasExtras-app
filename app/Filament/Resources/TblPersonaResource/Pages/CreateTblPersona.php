<?php

namespace App\Filament\Resources\TblPersonaResource\Pages;

use App\Filament\Resources\TblPersonaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateTblPersona extends CreateRecord
{
    protected static string $resource = TblPersonaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asignar contraseña por defecto '1234' automáticamente
        $data['password'] = Hash::make('1234');

        // Asignar rol por defecto como Usuario (tipo 1)
        $data['id_rol'] = 1;

        return $data;
    }
}

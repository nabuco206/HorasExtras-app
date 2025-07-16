<?php

namespace App\Filament\Resources\TblPersonaResource\Pages;

use App\Filament\Resources\TblPersonaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTblPersona extends EditRecord
{
    protected static string $resource = TblPersonaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(), // Comentado: Se usa flag_activo en lugar de eliminar
        ];
    }
}

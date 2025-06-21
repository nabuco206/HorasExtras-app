<?php

namespace App\Filament\Resources\TblTipoTrabajoResource\Pages;

use App\Filament\Resources\TblTipoTrabajoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTblTipoTrabajo extends EditRecord
{
    protected static string $resource = TblTipoTrabajoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

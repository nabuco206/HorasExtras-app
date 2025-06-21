<?php

namespace App\Filament\Resources\TblTipoTrabajoResource\Pages;

use App\Filament\Resources\TblTipoTrabajoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTblTipoTrabajos extends ListRecords
{
    protected static string $resource = TblTipoTrabajoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

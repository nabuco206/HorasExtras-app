<?php

namespace App\Filament\Resources\TblPersonaResource\Pages;

use App\Filament\Resources\TblPersonaResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTblPersonas extends ListRecords
{
    protected static string $resource = TblPersonaResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

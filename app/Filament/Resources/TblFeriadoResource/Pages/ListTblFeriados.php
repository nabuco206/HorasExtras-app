<?php

namespace App\Filament\Resources\TblFeriadoResource\Pages;

use App\Filament\Resources\TblFeriadoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTblFeriados extends ListRecords
{
    protected static string $resource = TblFeriadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\TblLiderResource\Pages;

use App\Filament\Resources\TblLiderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTblLiders extends ListRecords
{
    protected static string $resource = TblLiderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

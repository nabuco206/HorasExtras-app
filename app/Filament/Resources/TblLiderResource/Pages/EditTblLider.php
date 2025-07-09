<?php

namespace App\Filament\Resources\TblLiderResource\Pages;

use App\Filament\Resources\TblLiderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTblLider extends EditRecord
{
    protected static string $resource = TblLiderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

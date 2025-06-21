<?php

namespace App\Filament\Resources\TblFeriadoResource\Pages;

use App\Filament\Resources\TblFeriadoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTblFeriado extends CreateRecord
{
    protected static string $resource = TblFeriadoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['fecha'] = $data['dia'] . '-' . $data['mes'];
        unset($data['dia'], $data['mes']);
        return $data;
    }
}

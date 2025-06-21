<?php

namespace App\Filament\Resources\TblFeriadoResource\Pages;

use App\Filament\Resources\TblFeriadoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTblFeriado extends EditRecord
{
    protected static string $resource = TblFeriadoResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['fecha'] = $data['dia'] . '-' . $data['mes'];
        unset($data['dia'], $data['mes']);
        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['fecha']) && strpos($data['fecha'], '-') !== false) {
            [$data['dia'], $data['mes']] = explode('-', $data['fecha']);
        }
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

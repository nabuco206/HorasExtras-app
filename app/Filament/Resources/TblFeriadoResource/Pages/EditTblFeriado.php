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
        $data['fecha'] = $data['mes'] . '-' . $data['dia'];
        unset($data['dia'], $data['mes']);
        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (isset($data['fecha']) && strpos($data['fecha'], '-') !== false) {
            [$data['mes'], $data['dia']] = explode('-', $data['fecha']);
        }
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(), // Comentado: Se usa flag_activo en lugar de eliminar
        ];
    }
}

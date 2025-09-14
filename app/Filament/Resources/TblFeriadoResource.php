<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TblFeriadoResource\Pages;
use App\Filament\Resources\TblFeriadoResource\RelationManagers;
use App\Models\TblFeriado;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TblFeriadoResource extends Resource
{
    protected static ?string $model = TblFeriado::class;

    protected static ?string $navigationLabel = 'Feriados';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string
    {
        return 'Feriado';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Feriados';
    }

    public static function form(Form $form): Form
    {
        $dias = collect(range(1, 31))->mapWithKeys(fn($d) => [str_pad($d, 2, '0', STR_PAD_LEFT) => str_pad($d, 2, '0', STR_PAD_LEFT)]);
        $meses = [
            '01' => 'Enero',
            '02' => 'Febrero',
            '03' => 'Marzo',
            '04' => 'Abril',
            '05' => 'Mayo',
            '06' => 'Junio',
            '07' => 'Julio',
            '08' => 'Agosto',
            '09' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre',
        ];

        return $form
            ->schema([
                Forms\Components\Select::make('mes')
                    ->label('Mes')
                    ->options($meses)
                    ->required()
                    ->helperText('Seleccione el mes del feriado'),

                Forms\Components\Select::make('dia')
                    ->label('Día')
                    ->options($dias)
                    ->required()
                    ->helperText('Seleccione el día del feriado'),

                Forms\Components\TextInput::make('descripcion')
                    ->label('Descripción')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Descripción del feriado'),

                Forms\Components\Toggle::make('flag_activo')
                    ->label('Estado Activo')
                    ->default(true)
                    ->helperText('Indica si el feriado está activo en el sistema'),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha')
                    ->label('Fecha')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '';
                        $meses = [
                            '01' => 'Enero',
                            '02' => 'Febrero',
                            '03' => 'Marzo',
                            '04' => 'Abril',
                            '05' => 'Mayo',
                            '06' => 'Junio',
                            '07' => 'Julio',
                            '08' => 'Agosto',
                            '09' => 'Septiembre',
                            '10' => 'Octubre',
                            '11' => 'Noviembre',
                            '12' => 'Diciembre',
                        ];
                        $partes = explode('-', $state);
                        if (count($partes) == 2) {
                            $mes = $meses[$partes[0]] ?? $partes[0];
                            $dia = ltrim($partes[1], '0');
                            return "$dia de $mes";
                        }
                        return $state;
                    })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\IconColumn::make('flag_activo')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('flag_activo')
                    ->label('Estado')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn ($record) => $record->flag_activo ? 'Desactivar' : 'Activar')
                    ->icon(fn ($record) => $record->flag_activo ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($record) => $record->flag_activo ? 'warning' : 'success')
                    ->action(function ($record) {
                        $record->update(['flag_activo' => !$record->flag_activo]);
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->flag_activo ? 'Desactivar Feriado' : 'Activar Feriado')
                    ->modalDescription(fn ($record) => $record->flag_activo
                        ? '¿Está seguro de que desea desactivar este feriado?'
                        : '¿Está seguro de que desea activar este feriado?'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activar seleccionados')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['flag_activo' => true]));
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Desactivar seleccionados')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['flag_activo' => false]));
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTblFeriados::route('/'),
            'create' => Pages\CreateTblFeriado::route('/create'),
            'edit' => Pages\EditTblFeriado::route('/{record}/edit'),
        ];
    }
}

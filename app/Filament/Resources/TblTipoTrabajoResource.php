<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TblTipoTrabajoResource\Pages;
use App\Filament\Resources\TblTipoTrabajoResource\RelationManagers;
use App\Models\TblTipoTrabajo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TblTipoTrabajoResource extends Resource
{
    protected static ?string $model = TblTipoTrabajo::class;

    protected static ?string $navigationLabel = 'Tipo de Trabajo';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string
    {
        return 'Tipo de Trabajo';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Tipos de Trabajo';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('gls_tipo_trabajo')
                    ->label('Tipo de Trabajo')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Ingrese el nombre del tipo de trabajo'),
                    
                Forms\Components\Toggle::make('flag_activo')
                    ->label('Estado Activo')
                    ->default(true)
                    ->helperText('Indica si el tipo de trabajo está activo en el sistema'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('gls_tipo_trabajo')
                    ->label('Tipo de Trabajo')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('flag_activo')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->modalHeading(fn ($record) => $record->flag_activo ? 'Desactivar Tipo de Trabajo' : 'Activar Tipo de Trabajo')
                    ->modalDescription(fn ($record) => $record->flag_activo 
                        ? '¿Está seguro de que desea desactivar este tipo de trabajo?' 
                        : '¿Está seguro de que desea activar este tipo de trabajo?'),
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
            'index' => Pages\ListTblTipoTrabajos::route('/'),
            'create' => Pages\CreateTblTipoTrabajo::route('/create'),
            'edit' => Pages\EditTblTipoTrabajo::route('/{record}/edit'),
        ];
    }
}

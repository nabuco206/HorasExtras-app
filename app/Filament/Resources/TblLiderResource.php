<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TblLiderResource\Pages;
use App\Models\TblLider;
use App\Models\TblFiscalia;
use App\Models\TblPersona;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TblLiderResource extends Resource
{
    protected static ?string $model = TblLider::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationLabel = 'Líderes';

    public static function getModelLabel(): string
    {
        return 'Líder';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Líderes';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('cod_fiscalia')
                    ->label('Fiscalía')
                    ->required()
                    ->relationship('fiscalia', 'gls_fiscalia')
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('persona_id', null))
                    ->helperText('Seleccione primero la fiscalía para ver las personas habilitadas'),
                    
                Forms\Components\Select::make('persona_id')
                    ->label('Persona')
                    ->required()
                    ->options(function (Get $get) {
                        $fiscaliaId = $get('cod_fiscalia');
                        if (!$fiscaliaId) {
                            return [];
                        }
                        
                        return TblPersona::where('flag_lider', true)
                            ->where('flag_activo', true)
                            ->where('cod_fiscalia', $fiscaliaId)
                            ->get()
                            ->mapWithKeys(function ($record) {
                                return [$record->id => "{$record->Nombre} {$record->Apellido}"];
                            });
                    })
                    ->searchable()
                    ->helperText('Solo se muestran personas habilitadas como líderes y activas en la fiscalía seleccionada'),
                    
                Forms\Components\TextInput::make('gls_unidad')
                    ->label('Unidad')
                    ->maxLength(255),
                    
                Forms\Components\Toggle::make('flag_activo')
                    ->label('Estado Activo')
                    ->default(true)
                    ->helperText('Indica si el líder está activo en el sistema'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('persona.Nombre')
                    ->label('Persona')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->persona ? "{$record->persona->Nombre} {$record->persona->Apellido}" : 'Sin persona'),
                    
                Tables\Columns\TextColumn::make('username')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('fiscalia.gls_fiscalia')
                    ->label('Fiscalía')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('gls_unidad')
                    ->label('Unidad')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('flag_activo')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cod_fiscalia')
                    ->label('Fiscalía')
                    ->relationship('fiscalia', 'gls_fiscalia'),
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
                    ->modalHeading(fn ($record) => $record->flag_activo ? 'Desactivar Líder' : 'Activar Líder')
                    ->modalDescription(fn ($record) => $record->flag_activo 
                        ? '¿Está seguro de que desea desactivar este líder?' 
                        : '¿Está seguro de que desea activar este líder?'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTblLiders::route('/'),
            'create' => Pages\CreateTblLider::route('/create'),
            'edit' => Pages\EditTblLider::route('/{record}/edit'),
        ];
    }
}

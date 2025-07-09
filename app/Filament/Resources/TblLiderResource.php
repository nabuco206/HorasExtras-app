<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TblLiderResource\Pages;
use App\Models\TblLider;
use App\Models\TblFiscalia;
use App\Models\TblPersona;
use Filament\Forms;
use Filament\Forms\Form;
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
                Forms\Components\Select::make('persona_id')
                    ->label('Persona')
                    ->required()
                    ->relationship('persona', 'Nombre')
                    ->getOptionLabelFromRecordUsing(fn (TblPersona $record) => "{$record->Nombre} {$record->Apellido}")
                    ->searchable(['Nombre', 'Apellido', 'UserName'])
                    ->preload()
                    ->helperText('El nombre de usuario se asignará automáticamente'),
                    
                Forms\Components\Select::make('cod_fiscalia')
                    ->label('Fiscalía')
                    ->required()
                    ->relationship('fiscalia', 'gls_fiscalia')
                    ->preload(),
                    
                Forms\Components\TextInput::make('gls_unidad')
                    ->label('Unidad')
                    ->maxLength(255),
                    
                Forms\Components\Toggle::make('activo')
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
                    
                Tables\Columns\IconColumn::make('activo')
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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

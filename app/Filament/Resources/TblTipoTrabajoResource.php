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
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->label('ID'),
                Tables\Columns\TextColumn::make('gls_tipo_trabajo')->label('Tipo de Trabajo')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d/m/Y H:i')->label('Creado')->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime('d/m/Y H:i')->label('Actualizado')->sortable(),
            ])
            ->filters([
                //
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

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
                Forms\Components\Select::make('dia')
                    ->label('Día')
                    ->options($dias)
                    ->required(),
                Forms\Components\Select::make('mes')
                    ->label('Mes')
                    ->options($meses)
                    ->required(),
                Forms\Components\TextInput::make('descripcion')
                    ->label('Descripción')
                    ->required()
                    ->maxLength(255),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->label('ID'),
                Tables\Columns\TextColumn::make('fecha')->label('Fecha')->sortable(),
                Tables\Columns\TextColumn::make('descripcion')->label('Descripción')->searchable()->sortable(),
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
            'index' => Pages\ListTblFeriados::route('/'),
            'create' => Pages\CreateTblFeriado::route('/create'),
            'edit' => Pages\EditTblFeriado::route('/{record}/edit'),
        ];
    }
}

<?php
// archivo tbl_persona_resource.php
namespace App\Filament\Resources;

use App\Filament\Resources\TblPersonaResource\Pages;
use App\Filament\Resources\TblPersonaResource\RelationManagers;
use App\Models\TblPersona;
use App\Models\TblFiscalia;
use App\Models\TblEscalafon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;

class TblPersonaResource extends Resource
{
    protected static ?string $model = TblPersona::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('Nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('Apellido')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('UserName')
                    ->required()
                    ->maxLength(255),
                Select::make('cod_fiscalia')
                    ->label('Fiscalía')
                    ->options(
                        TblFiscalia::query()
                            ->whereNotNull('gls_fiscalia')
                            ->where('gls_fiscalia', '!=', '')
                            ->pluck('gls_fiscalia', 'id')
                            ->toArray()
                    )
                    ->searchable()
                    ->required(),
                Select::make('id_escalafon')
                    ->label('Escalafón')
                    ->options(
                        TblEscalafon::query()
                            ->whereNotNull('gls_escalafon')
                            ->where('gls_escalafon', '!=', '')
                            ->pluck('gls_escalafon', 'id')
                            ->toArray()
                    )
                    ->searchable()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('Nombre')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('Apellido')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('UserName')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('fiscalia.gls_fiscalia')
                    ->label('Fiscalía')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('escalafon.gls_escalafon')
                    ->label('Escalafón')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                // Agrega filtros si es necesario
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Define relaciones si es necesario
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTblPersonas::route('/'),
            'create' => Pages\CreateTblPersona::route('/create'),
            'edit' => Pages\EditTblPersona::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return 'Persona';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Personas';
    }
}

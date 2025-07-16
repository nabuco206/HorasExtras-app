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

    protected static ?string $navigationLabel = 'Personas';
    protected static ?string $navigationGroup = 'Gestión de usuarios'; // puedes poner otro nombre o quitarla si no la usas

    public static function getModelLabel(): string
    {
        return 'Persona';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Personas';
    }


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
                Forms\Components\Toggle::make('flag_lider')
                    ->label('Puede ser Líder')
                    ->default(false)
                    ->helperText('Indica si la persona puede ser asignada como líder'),
                Forms\Components\Toggle::make('flag_activo')
                    ->label('Estado Activo')
                    ->default(true)
                    ->helperText('Indica si la persona está activa en el sistema'),
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
                Tables\Columns\IconColumn::make('flag_lider')
                    ->label('Líder')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                Tables\Columns\IconColumn::make('flag_activo')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('flag_lider')
                    ->label('Puede ser Líder')
                    ->trueLabel('Solo personas que pueden ser líderes')
                    ->falseLabel('Solo personas que NO pueden ser líderes')
                    ->native(false),
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
                    ->modalHeading(fn ($record) => $record->flag_activo ? 'Desactivar Persona' : 'Activar Persona')
                    ->modalDescription(fn ($record) => $record->flag_activo 
                        ? '¿Está seguro de que desea desactivar esta persona?' 
                        : '¿Está seguro de que desea activar esta persona?'),
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
}

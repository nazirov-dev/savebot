<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LangResource\Pages;
use App\Filament\Resources\LangResource\RelationManagers;
use App\Models\Lang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LangResource extends Resource
{
    protected static ?string $model = Lang::class;

    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $navigationLabel = 'Tillar';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Forms\Components\TextInput::make('name')->label('Til nomi'),
            Forms\Components\TextInput::make('short_code')->label('Til qisqa kodi')->placeholder('Masalan: uz'),
            Forms\Components\Toggle::make('status')->label('Aktivmi?')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Til nomi'),
                Tables\Columns\TextColumn::make('short_code')->label('Til qisqa kodi'),
                Tables\Columns\ToggleColumn::make('status')->label('Aktivmi?')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ViewAction::make()
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
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
            'index' => Pages\ListLangs::route('/'),
            'create' => Pages\CreateLang::route('/create'),
            'edit' => Pages\EditLang::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BotUserResource\Pages;
use App\Filament\Resources\BotUserResource\RelationManagers;
use App\Models\Text;
use App\Models\BotUser;
use App\Models\Lang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BotUserResource extends Resource
{
    protected static ?string $model = BotUser::class;
    protected static ?string $navigationIcon = 'heroicon-m-user-group';
    protected static ?string $navigationLabel = 'Foydalanuvchilar';

    public static function canCreate(): bool
    {
        return false;
    }
    public static function form(Form $form): Form
    {
        $langs = Lang::pluck('name', 'short_code')
        ->toArray();
        return $form
            ->schema([
                    Forms\Components\TextInput::make('user_id')
                        ->label('Foydalanuvchi ID raqami')
                        ->numeric(),
                    Forms\Components\Select::make('lang_code')
                        ->options($langs)
                        ->label('Tili'),
                    Forms\Components\TextInput::make('name')
                        ->label('Ismi'),

                    Forms\Components\TextInput::make('username')
                        ->label("Username"),

                    Forms\Components\Toggle::make('status')
                    ->label("Aktivmi?"),

                    Forms\Components\DateTimePicker::make('created_at')
            ]);
    }

    public static function table(Table $table): Table
    {
        $langs = Lang::pluck('name', 'short_code')
            ->toArray();
        define('LANG', $langs);
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                        ->label('Foydalanuvchi tartib raqami'),
                Tables\Columns\TextColumn::make('user_id')
                    ->label('Foydalanuvchi ID raqami'),
                Tables\Columns\TextColumn::make('lang_code')
                    ->formatStateUsing(function ($record): string {
                        return LANG[$record->lang_code];
                    })
                    ->label('Tili'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Ismi'),

                Tables\Columns\TextColumn::make('username')
                    ->label("Username")
                    ->url(fn($record) => "https://t.me/{$record->username}")
                    ->prefix('@'),
                Tables\Columns\ToggleColumn::make('status')
                ->label("Aktivmi?"),

                Tables\Columns\TextColumn::make('created_at')
                ->label("Ro'yhatdan o'tgan vaqti")
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ViewAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
            ])
            ->paginated([10, 25, 50, 100])
            ->defaultSort('id', 'desc');
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
            'index' => Pages\ListBotUsers::route('/'),
            'create' => Pages\CreateBotUser::route('/create'),
            'edit' => Pages\EditBotUser::route('/{record}/edit'),
        ];
    }
}

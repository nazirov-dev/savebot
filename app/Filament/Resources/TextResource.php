<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TextResource\Pages;
use App\Filament\Resources\TextResource\RelationManagers;
use App\Models\Text;
use App\Models\Lang;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TextResource extends Resource
{
    protected static ?string $model = Text::class;

    protected static ?string $navigationIcon = 'heroicon-s-chat-bubble-bottom-center-text';
    protected static ?string $navigationLabel = 'Matnlar';


    public static function form(Form $form): Form
    {
        $langs = Lang::pluck('name', 'short_code')
        ->toArray();
        $lang_code = session()->has('lang_code') ? session('lang_code') : null;
        session()->forget('lang_code');

        return $form
            ->schema([
                Forms\Components\Select::make('lang_code')
                ->options($langs)
                ->default($lang_code)
                ->label('Tilni tanlang'),
                    Forms\Components\TextInput::make('subscribe_to_forced_channels')
                        ->label('Subscribe to Forced Channels')
                        ->hint("⚠️ Ushbu botdan foydalanish uchun quyidagi kanalga a’zo bo‘ling. Keyin <b>\"A’zo bo‘ldim ✅\"</b> tugmasini bosing."),

                    Forms\Components\TextInput::make('ad_text')
                        ->label('Ad Text')
                        ->hint('🤖 @ALLSAVEUZ_Bot orqali yuklab olindi.'),

                    Forms\Components\TextInput::make('language_changed')
                        ->label('Language Changed')
                        ->hint('Til o\'zgartirildi ✅'),

                    Forms\Components\TextInput::make('you_are_still_not_member')
                        ->label('You Are Still Not a Member')
                        ->hint('Siz hali a\'zo bo\'lmagansiz'),

                    Forms\Components\TextInput::make('check_button_label')
                        ->label('Check Button Label')
                        ->hint('A’zo bo‘ldim ✅'),

                    Forms\Components\TextInput::make('cancel_button_label')
                        ->label('Cancel Button Label')
                        ->hint('Bekor qilish ❌'),
                    Forms\Components\TextInput::make('select_language')
                        ->label('Cancel Button Label')
                        ->hint('<b>🇺🇿 O’zingizga qulay bo’lgan tilni tanlang.</b>'),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')->label('Kalit so\'z'),
                Tables\Columns\TextColumn::make('value')->label('Matn')->html()->listWithLineBreaks()->limit(50),
                Tables\Columns\TextColumn::make('lang_code')->label('Til kodi')
            ])
            ->filters([
                //
            ])
            ->actions([
                    Tables\Actions\EditAction::make()
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
            'index' => Pages\ListTexts::route('/'),
            // 'create' => Pages\CreateAllTextForLang::route('/create'),
            'create' => Pages\CreateText::route('/create'),
            'edit' => Pages\EditText::route('/{record}/edit'),
        ];
    }
}

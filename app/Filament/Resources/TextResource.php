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
                    Forms\Components\TextInput::make('start_text')
                        ->label('/start komandasidagi matn')
                        ->hint("<b>🤖 Assalomu aleykum hurmatli foydalanuvchi, Bot orqali siz yuklab olishingiz mumkin:</b>

• Instagram - stories, post va IGTV;
• YouTube - video formatda;
• TikTok - video;
• Likee - video;
• Pinterest - rasm, video va gif;
• Facebook - video;

<b>🚀 Media yuklash uchun shunchaki uning havolasini yuboring:</b>"),
                    Forms\Components\TextInput::make('subscribe_to_forced_channels')
                        ->label('Kanalga azo bo\'lish haqida matn')
                        ->hint("⚠️ Ushbu botdan foydalanish uchun quyidagi kanalga a’zo bo‘ling. Keyin <b>\"A’zo bo‘ldim ✅\"</b> tugmasini bosing."),

                    Forms\Components\TextInput::make('ad_text')
                        ->label('Reklama matni')
                        ->hint('🤖 @ALLSAVEUZ_Bot orqali yuklab olindi.'),

                    Forms\Components\TextInput::make('language_changed')
                        ->label('Til almashtirilganlik haqida matn')
                        ->hint('Til o\'zgartirildi ✅'),

                    Forms\Components\TextInput::make('you_are_still_not_member')
                        ->label('Kanalga a\'zo bo\'lmaganlik haqida matn')
                        ->hint('Siz hali a\'zo bo\'lmagansiz'),

                    Forms\Components\TextInput::make('check_button_label')
                        ->label('Azolikni tekshirish knopkasini matni')
                        ->hint('A’zo bo‘ldim ✅'),

                    Forms\Components\TextInput::make('cancel_button_label')
                        ->label('Bekor qilish tugmasini matni')
                        ->hint('Bekor qilish ❌'),
                    Forms\Components\TextInput::make('select_language')
                        ->label('Tilni tanlash tog\'risidagi matn')
                        ->hint('<b>🇺🇿 O’zingizga qulay bo’lgan tilni tanlang.</b>'),
                    Forms\Components\TextInput::make('unable_to_download_video')
                        ->label('Video yuklab olib bo\'lmasligi haqida matn')
                        ->hint('<b>Yuklab olish imkoni mavjud emas, iltimos keyinroq urining!</b>'),
                    Forms\Components\TextInput::make('invalid_url')
                        ->label('Yuborilgan link notog\'ri ekanligi haqida matn')
                        ->hint('<b>Siz yuborgan havola notog\'ri iltimos tekshirib qaytadan urinib ko\'ring.</b>'),
                    Forms\Components\TextInput::make('progress_text')
                        ->label('Yuklash boshlanganligi haqida matn')
                        ->hint('<b>Serverga yuklanmoqda...</b>'),
                    Forms\Components\TextInput::make('large_than_50mb')
                        ->label('50 Mbdan katta video kelgandagi xatolik matni')
                        ->hint('<b>Video hajmi 50mb dan yuqori, yuklab olish imkoni mavjud emas.</b>'),
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
            ])
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
            'index' => Pages\ListTexts::route('/'),
            // 'create' => Pages\CreateAllTextForLang::route('/create'),
            'create' => Pages\CreateText::route('/create'),
            'edit' => Pages\EditText::route('/{record}/edit'),
        ];
    }
}

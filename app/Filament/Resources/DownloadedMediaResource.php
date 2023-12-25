<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DownloadedMediaResource\Pages;
use App\Filament\Resources\DownloadedMediaResource\RelationManagers;
use App\Models\Downloaded_Media;
use Filament\Notifications\Notification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Http;

class DownloadedMediaResource extends Resource
{
    protected static ?string $model = Downloaded_Media::class;

    protected static ?string $navigationIcon = 'heroicon-m-inbox-arrow-down';
    protected static ?string $navigationLabel = 'Yuklangan medialar';

    public static function canCreate(): bool
    {
        return false;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('url')
                ->url()
                ->label('URL'),

            Forms\Components\TextInput::make('media_id')
                ->label('Media ID'),

            Forms\Components\TextInput::make('user_id')
                ->label('User ID')
                ->numeric(),

            Forms\Components\TextInput::make('platform_id')
                ->label('Platforma ID')
                ->numeric(),

            Forms\Components\TextInput::make('media_group_id')
                ->label('Media Group ID'),

            Forms\Components\TextInput::make('type')
                ->label('Turi'),

            Forms\Components\Textarea::make('description')
                ->label('Izoh'),
            ]);
    }

    public static function table(Table $table): Table
    {
        $notification = "";
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->url(fn($record) => $record->url)
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('user_id')
                    ->label('User ID')
                    ->searchable()
                    ->url(fn($record) => BotUserResource::getUrl('edit', ['record' => $record->user_id])),

                Tables\Columns\TextColumn::make('description')
                    ->label('Izoh')
                    ->searchable()
                    ->toggleable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('platform_id')
                    ->label('Platforma ID')
                    ->searchable(),


                Tables\Columns\TextColumn::make('type')
                    ->label('Turi')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('media_id')
                    ->label('Media ID')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('media_group_id')
                    ->label('Media Group ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('sendMedia')
                        ->icon('heroicon-o-paper-airplane')
                        ->action(function (Downloaded_Media $record) {
                            $response = Http::post(route('sendMedia'), [
                                'file_id' => $record->media_id,
                                'type' => $record->type
                            ]);
                            if ($response->successful()) {
                                $jsonData = $response->json();
                                if($jsonData['ok']) {
                                    Notification::make()
                                        ->title('Media telegram orqali sizga jo\'natildi')
                                        ->success()
                                        ->duration(5000)
                                        ->color('success')
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Mediani yuborib bo\'lmadi, telegram bilana qandaydir xatolik yuz berdi')
                                        ->danger()
                                        ->duration(10000)
                                        ->color('danger')
                                        ->send();
                                }
                            } else {
                                Notification::make()
                                        ->title("Internet bilan bog'liq muammo bor!")
                                        ->danger()
                                        ->duration(5000)
                                        ->color('danger')
                                        ->send();
                            }
                        }),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make()
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->emptyStateHeading('Media fayl yuklab olinmagan')
            ->emptyStateDescription("Botdan hali hech kim media fayl yuklab olmadi");
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
            'index' => Pages\ListDownloadedMedia::route('/'),
            'create' => Pages\CreateDownloadedMedia::route('/create'),
            'edit' => Pages\EditDownloadedMedia::route('/{record}/edit'),
        ];
    }
}

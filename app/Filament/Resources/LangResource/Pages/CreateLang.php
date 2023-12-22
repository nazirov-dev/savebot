<?php

namespace App\Filament\Resources\LangResource\Pages;

use App\Filament\Resources\LangResource;
use App\Filament\Resources\TextResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLang extends CreateRecord
{
    protected static string $resource = LangResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array{
        session(['lang_code' => $data['short_code']]);
        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return TextResource::getUrl('create');
    }
}

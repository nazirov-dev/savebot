<?php

namespace App\Filament\Resources\DownloadedMediaResource\Pages;

use App\Filament\Resources\DownloadedMediaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDownloadedMedia extends EditRecord
{
    protected static string $resource = DownloadedMediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\TextResource\Pages;

use App\Models\Text;
use App\Filament\Resources\TextResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditText extends EditRecord
{
    protected static string $resource = TextResource::class;
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $get_all_text = Text::where(['lang_code' => $data['lang_code']])->get()->toArray();
        $data = ['lang_code' => $data['lang_code']];
        foreach ($get_all_text as $value) {
            $data[$value['key']] = $value['value'];
        }

        return $data;
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $lang_code = $data['lang_code'];
        unset($data['lang_code']);



        foreach ($data as $key => $value) {
            // Find the Text model instance by key and lang_code and update it
            Text::where(['key' => $key, 'lang_code' => $lang_code])->update([
                'value' => $value
            ]);
        }
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}

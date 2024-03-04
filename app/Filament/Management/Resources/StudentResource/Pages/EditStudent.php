<?php

namespace App\Filament\Management\Resources\StudentResource\Pages;

use App\Filament\Management\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    public function getTitle(): string | Htmlable
    {
        return __('Edit student');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $name = $data['name'];
        $data['name'] = Str::ucwords(strtolower($name));

        $surname = $data['surname'];
        $data['surname'] = Str::ucwords(strtolower($surname));

        return $data;
    }
}

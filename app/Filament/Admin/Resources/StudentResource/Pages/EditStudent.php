<?php

namespace App\Filament\Admin\Resources\StudentResource\Pages;

use App\Filament\Admin\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;
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
            Actions\DeleteAction::make()->color(Color::hex('#c94f40')),
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

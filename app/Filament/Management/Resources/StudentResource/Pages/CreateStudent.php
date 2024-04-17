<?php

namespace App\Filament\Management\Resources\StudentResource\Pages;

use App\Filament\Management\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected static ?string $title = 'Create student';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $name = $data['name'];
        $data['name'] = Str::ucwords(strtolower($name));

        $surname = $data['surname'];
        $data['surname'] = Str::ucwords(strtolower($surname));

        if($data['email'] == null) {
            $data['email'] = 'pagospathexaminations@gmail.com';
        }
        
        return $data;
    }
}

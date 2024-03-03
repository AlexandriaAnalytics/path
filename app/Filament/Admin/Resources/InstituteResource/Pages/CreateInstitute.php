<?php

namespace App\Filament\Admin\Resources\InstituteResource\Pages;

use App\Filament\Admin\Resources\InstituteResource;
use App\Models\Country;
use App\Models\Institute;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateInstitute extends CreateRecord
{
    protected static string $resource = InstituteResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $country = Country::findOrFail($data['country']);
        $countryCode = strtoupper(substr($country->name, 0, 2));
        $lastInstitute = Institute::latest()->first()->unique_number;
        if ($lastInstitute == null || $lastInstitute == '') {
            $numeros = 300;
        } else {
            if (preg_match('/^(\d+)/', $lastInstitute, $matches)) {
                $numeros = $matches[1] + 1;
            }
        }


        $data['unique_number'] = $numeros . $countryCode . substr(date('Y'), -2);;

        return $data;
    }
}

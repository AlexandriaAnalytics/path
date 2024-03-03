<?php

namespace App\Filament\Admin\Resources\InstituteTypeResource\Pages;

use App\Filament\Admin\Resources\InstituteTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;

class EditInstituteType extends EditRecord
{
    protected static string $resource = InstituteTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->color(Color::hex('#c94f40')),
        ];
    }
}

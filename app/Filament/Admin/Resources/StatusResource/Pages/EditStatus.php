<?php

namespace App\Filament\Admin\Resources\StatusResource\Pages;

use App\Filament\Admin\Resources\StatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;

class EditStatus extends EditRecord
{
    protected static string $resource = StatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->color(Color::hex('#c94f40')),
        ];
    }
}

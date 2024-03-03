<?php

namespace App\Filament\Admin\Resources\LevelResource\Pages;

use App\Filament\Admin\Resources\LevelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;

class EditLevel extends EditRecord
{
    protected static string $resource = LevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->color(Color::hex('#c94f40')),
        ];
    }
}

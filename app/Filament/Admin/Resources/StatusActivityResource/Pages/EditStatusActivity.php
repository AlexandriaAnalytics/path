<?php

namespace App\Filament\Admin\Resources\StatusActivityResource\Pages;

use App\Filament\Admin\Resources\StatusActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStatusActivity extends EditRecord
{
    protected static string $resource = StatusActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}

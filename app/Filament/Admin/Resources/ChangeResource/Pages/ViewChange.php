<?php

namespace App\Filament\Admin\Resources\ChangeResource\Pages;

use App\Filament\Admin\Resources\ChangeResource;
use App\Models\Change;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewChange extends ViewRecord
{
    protected static string $resource = ChangeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark as resolved')
                ->icon('heroicon-o-check')
                ->action(function (array $data, Change $change) {
                    $change->status = 1;
                    $change->save();
                }),
        ];
    }
}

<?php

namespace App\Filament\Trainee\Resources\ActivityResource\Pages;

use App\Filament\Admin\Resources\ChangeResource;
use App\Filament\Trainee\Resources\ActivityResource;
use App\Models\Change;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewActivity extends ViewRecord
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

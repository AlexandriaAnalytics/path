<?php

namespace App\Filament\Trainee\Resources\ActivityResource\Pages;

use App\Filament\Trainee\Resources\ActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\Support\Htmlable;

class ListActivities extends ListRecords
{
    protected static string $resource = ActivityResource::class;

    protected ?string $subheading = 'Welcome to the Path training programme. Make sure you start and submit a section in one go. You are allowed to complete sections on different days.';

    public function getTitle(): string | Htmlable
    {
        return __('Path Training Programme');
    }

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}

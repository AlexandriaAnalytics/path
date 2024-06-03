<?php

namespace App\Filament\Trainee\Resources\FilesResource\Pages;

use App\Filament\Trainee\Resources\FilesResource;
use App\Models\Trainee;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFiles extends ListRecords
{
    protected static string $resource = FilesResource::class;

    protected static string $view = 'filament.trainee.files';

    protected function getViewData(): array
    {
        $trainee = Trainee::where('user_id', auth()->user()->id)->first();
        return array_merge(parent::getViewData(), [
            'externalUrl' => $trainee->files,
        ]);
    }
}

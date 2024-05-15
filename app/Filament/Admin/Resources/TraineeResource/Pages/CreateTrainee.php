<?php

namespace App\Filament\Admin\Resources\TraineeResource\Pages;

use App\Filament\Admin\Resources\TraineeResource;
use App\Models\Record;
use App\Models\StatusActivity;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateTrainee extends CreateRecord
{
    protected static string $resource = TraineeResource::class;

    public function getTitle(): string | Htmlable
    {
        return __('Create trainee');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        if ($this->record->sections) {
            $sections = $this->record->sections;
            foreach ($sections as $section) {
                $record = new Record();
                $record->trainee_id = $this->record->id;
                $record->section_id = $section;
                $record->status_id = StatusActivity::where('default', 1)->first()->id;
                $record->performance_id = null;
                $record->save();
            }
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['status']) {
            $data['status'] = 'active';
        } else {
            $data['status'] = 'inactive';
        }

        return $data;
    }
}

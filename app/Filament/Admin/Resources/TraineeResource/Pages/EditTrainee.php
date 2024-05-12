<?php

namespace App\Filament\Admin\Resources\TraineeResource\Pages;

use App\Filament\Admin\Resources\TraineeResource;
use App\Models\Record;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrainee extends EditRecord
{
    protected static string $resource = TraineeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        if ($this->record->sections) {
            $sections = $this->record->sections;
            foreach ($sections as $section) {
                if (Record::where('trainee_id', $this->record->id)->where('section_id', $section)->count() == 0) {
                    $record = new Record();
                    $record->trainee_id = $this->record->id;
                    $record->section_id = $section;
                    $record->status_id = 1;
                    $record->save();
                }
            }
            $borrarSections = array_diff(Record::where('trainee_id', $this->record->id)->pluck('id')->toArray(), $sections);
            if ($borrarSections != []) {
                foreach ($borrarSections as $section) {
                    Record::where('trainee_id', $this->record->id)->where('section_id', $section)->delete();
                }
            }
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['status']) {
            $data['status'] = 'active';
        } else {
            $data['status'] = 'inactive';
        }

        return $data;
    }
}

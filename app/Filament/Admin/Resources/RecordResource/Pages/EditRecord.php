<?php

namespace App\Filament\Admin\Resources\RecordResource\Pages;

use App\Filament\Admin\Resources\RecordResource;
use App\Models\Trainee;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord as BaseEditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditRecord extends BaseEditRecord
{
    protected static string $resource = RecordResource::class;

    public function getTitle(): string | Htmlable
    {
        return __('Edit record');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['trainee_id'] = Trainee::find($data['trainee_id'])->user->name;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['trainee_id'] = $this->record->trainee_id;
        return $data;
    }

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
}

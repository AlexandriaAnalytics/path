<?php

namespace App\Filament\Admin\Resources\ExaminerActivityResource\Pages;

use App\Filament\Admin\Resources\ExaminerActivityResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateExaminerActivity extends CreateRecord
{
    protected static string $resource = ExaminerActivityResource::class;

    public function getTitle(): string | Htmlable
    {
        return __('Create examiner activity');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

<?php

namespace App\Filament\Admin\Resources\ExamSessionResource\Pages;

use App\Filament\Admin\Resources\ExamSessionResource;
use App\Models\Institute;
use Filament\Actions;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ViewExamSession extends ViewRecord
{
    protected static string $resource = ExamSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

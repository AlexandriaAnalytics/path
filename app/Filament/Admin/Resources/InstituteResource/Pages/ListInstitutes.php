<?php

namespace App\Filament\Admin\Resources\InstituteResource\Pages;

use App\Models\InstituteType;
use App\Filament\Admin\Resources\InstituteResource;
use Filament\Actions;
use Filament\Resources\Components;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListInstitutes extends ListRecords
{
    protected static string $resource = InstituteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('New members and centres'),
            Actions\Action::make('download')
            ->label('Download Excel All Members')
            ->color('success')
            ->outlined()
            ->url('/members-excel'),
        ];
    }

    public function getTabs(): array
    {


        // Create tabs for each institute type.
        return [
            'All' => Components\Tab::make(),
            ...InstituteType::all()->mapWithKeys(fn (InstituteType $instituteType) => [
                $instituteType->name => Components\Tab::make()
                    ->label($instituteType->name)
                    ->modifyQueryUsing(fn ($query) => $query->where('institute_type_id', $instituteType->id)),
            ]),
        ];
    }
}

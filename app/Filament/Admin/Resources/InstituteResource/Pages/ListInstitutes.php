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
            Actions\CreateAction::make(),
        ];
    }

    public function getSubheading(): string|Htmlable|null
    {
        return str('Create, update, and delete institutes, and manage the authorised users of each institute.');
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

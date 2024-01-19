<?php

namespace App\Filament\Admin\Resources\InstituteResource\Pages;

use App\Enums\InstituteType;
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
        $instituteTypes = collect(InstituteType::cases());

        // Create tabs for each institute type.
        return [
            'All' => Components\Tab::make(),
            ...$instituteTypes->mapWithKeys(fn (InstituteType $instituteType) => [
                $instituteType->value => Components\Tab::make()
                    ->label($instituteType->getLabel())
                    ->modifyQueryUsing(fn ($query) => $query->whereType($instituteType)),
            ]),
        ];
    }
}

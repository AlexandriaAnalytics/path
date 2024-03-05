<?php

namespace App\Filament\Management\Pages\Tenancy;

use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\EditTenantProfile;

class EditInstituteProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'My profile';
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Fieldset::make('General information')
                    ->columnSpan(1)
                    ->columns(1)
                    ->schema([
                        TextInput::make('unique_number')
                            ->label('Unique ID')
                            ->disabled(),
                        TextInput::make('name')
                            ->label('Institution')
                            ->disabled(),
                        Select::make('instituteType')
                            ->label('Membership type')
                            ->relationship('instituteType', 'name')
                            ->disabled(),
                    ]),
                Fieldset::make('Contact information')
                    ->columnSpan(1)
                    ->columns(1)
                    ->schema([
                        TextInput::make('email')
                            ->label('Email'),
                        TextInput::make('phone')
                            ->label('Phone'),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Admin\Pages;

use App\Settings\InformationSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageInformation extends SettingsPage
{
    protected static ?string $title = 'Centres\' Information';

    protected static ?string $navigationGroup = 'Settings';

    protected static string $settings = InformationSettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('files_url')
                    ->label('Files URL')
                    ->hint('The URL to the files for all centres.')
                    ->maxLength(255)
                    ->url()
                    ->required(),
            ]);
    }
}

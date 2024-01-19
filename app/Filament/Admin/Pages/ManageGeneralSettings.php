<?php

namespace App\Filament\Admin\Pages;

use App\Settings\GeneralSettings;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageGeneralSettings extends SettingsPage
{
    protected static ?string $title = 'General';

    protected static ?string $navigationGroup = 'Settings';

    protected static string $settings = GeneralSettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('client_link')
                    ->label('Client Link')
                    ->placeholder('https://client.example.com'),
            ]);
    }
}

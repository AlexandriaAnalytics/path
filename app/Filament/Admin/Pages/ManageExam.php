<?php

namespace App\Filament\Admin\Pages;

use App\Settings\ExamSettings;
use Filament\Forms;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageExam extends SettingsPage
{
    protected static ?string $title = 'Exam Settings';

    protected static ?string $navigationGroup = 'Settings';

    protected static string $settings = ExamSettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Toggle::make('registrations_open')
                    ->label('Registrations Open')
                    ->helperText('Allow candidates to be registered for exams.'),
            ]);
    }
}

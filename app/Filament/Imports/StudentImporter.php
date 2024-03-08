<?php

namespace App\Filament\Imports;

use App\Models\Student;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class StudentImporter extends Importer
{
    protected static ?string $model = Student::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->exampleHeader('Name')
                ->example('John'),
            ImportColumn::make('surname')
                ->label('Surname')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->exampleHeader('Surname')
                ->example('Doe'),
            ImportColumn::make('email')
                ->label('Email address')
                ->rules(['email', 'max:255', 'unique:students,email', 'nullable'])
                ->exampleHeader('Email address')
                ->example('john.doe@example.com'),
            ImportColumn::make('birth_date')
                ->label('Date of birth')
                ->requiredMapping()
                ->rules(['required'])
                ->exampleHeader('Date of birth')
                ->example('23/04/1999')
                ->castStateUsing(function (string $state): ?Carbon {
                    if (blank($state)) {
                        return null;
                    }

                    // Try to parse the date using the format `d/m/Y`.
                    try {
                        return Carbon::createFromFormat('d/m/Y', $state);
                    } catch (\Exception $e) {
                        Log::error('inport student exeption', [ $e]);
                        return null;
                    }
                }),
            ImportColumn::make('personal_educational_needs')
                ->label('Educational needs')
                ->rules(['max:255', 'nullable'])
                ->exampleHeader('Educational needs')
                ->example('Needs x, y, z')
                ->castStateUsing(function (string $state): ?string {
                    if (blank($state)) {
                        return null;
                    }

                    return $state;
                })
        ];
    }

    public function resolveRecord(): ?Student
    {
        // return Student::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return Student::make([
            'institute_id' => $this->options['institute_id'],
            'country_id' => $this->options['country_id'],
            'email' => $this->data['email'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your student import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('institute_id')
                ->label('Member or centre')
                ->relationship('institute', 'name')
                ->visible(fn () => Filament::getCurrentPanel()->getId() === 'admin')
                ->required()
                ->searchable()
                ->preload(),
            Select::make('country_id')
                ->relationship('country', 'name')
                ->required()
                ->searchable()
                ->preload(),
        ];
    }
}

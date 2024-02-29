<?php

namespace App\Filament\Imports;

use App\Models\Student;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Select;
use Illuminate\Support\Carbon;

class StudentImporter extends Importer
{
    protected static ?string $model = Student::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('John'),
            ImportColumn::make('surname')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('Doe'),
            ImportColumn::make('email')
                ->rules(['email', 'max:255', 'unique:students,email'])
                ->example('john.doe@example.com'),
            ImportColumn::make('birth_date')
                ->requiredMapping()
                ->rules(['required', 'date_format:d/m/Y'])
                ->example('23/04/1999')
                ->castStateUsing(function (string $state): ?Carbon {
                    if (blank($state)) {
                        return null;
                    }

                    // Try to parse the date using the format `d-m-Y`.
                    try {
                        return Carbon::createFromFormat('d-m-Y', $state);
                    } catch (\Exception $e) {
                        // If the date is not in the expected format, return the original value.
                        return null;
                    }
                }),
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
                ->relationship('institute', 'name')
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

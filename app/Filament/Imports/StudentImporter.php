<?php

namespace App\Filament\Imports;

use App\Models\Student;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Facades\Filament;

class StudentImporter extends Importer
{
    protected static ?string $model = Student::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('national_id')
                ->label('National ID')
                ->requiredMapping()
                ->rules(['required', 'max:32']),
            ImportColumn::make('first_name')
                ->label('First Name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('surnames')
                ->label('Last Name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('country')
                ->label('Country')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('address')
                ->label('Address')
                ->rules(['max:255']),
            ImportColumn::make('phone')
                ->label('Phone')
                ->rules(['max:255']),
            ImportColumn::make('cbu')
                ->label('CBU')
                ->rules(['max:22']),
            ImportColumn::make('birth_date')
                ->label('Birth Date')
                ->requiredMapping()
                ->rules(['required', 'date']),
        ];
    }

    public function resolveRecord(): ?Student
    {
        return Student::firstOrNew([
            // Update existing records, matching them by `$this->data['column_name']`
            'institute_id' => Filament::getTenant()->id,
            'national_id' => $this->data['national_id'],
        ]);

        // return new Student();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your student import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}

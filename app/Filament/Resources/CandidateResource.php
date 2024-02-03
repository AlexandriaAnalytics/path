<?php

namespace App\Filament\Resources;

use App\Enums\UserStatus;
use App\Exports\CandidateByIdExport;
use App\Filament\Admin\Resources\CandidateResource as AdminCandidateResource;
use App\Filament\Resources\CandidateResource\Pages;
use App\Models\Candidate;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Facades\Filament;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CandidateResource extends Resource
{
    protected static bool $isScopedToTenant = false;

    protected static ?string $model = Candidate::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Exam Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Student')
                    ->schema([
                        Select::make('student_id')
                            ->relationship(
                                name: 'student',
                                modifyQueryUsing: fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
                            )
                            ->getOptionLabelFromRecordUsing(fn (Student $record) => "{$record->names} {$record->last_name}")
                            ->searchable()
                            ->preload()
                            ->required()
                    ]),
                ...AdminCandidateResource::getExamFields(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //Candidate
                TextColumn::make('id')
                    ->label('Candidate No.')
                    ->sortable()
                    ->searchable()
                    ->numeric(),
                TextColumn::make('status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cancelled' => 'gray',
                        'unpaid' => 'danger',
                        'paid' => 'success',
                    }),
                TextColumn::make('modules.name')
                    ->badge()
                /* IconColumn::make('modules')
                    ->icon(function (Candidate $candidate) {
                        $modules = $candidate->modules;
                        $allModulesHaveExamSession = $modules->every(function ($module) use ($candidate) {
                            return $module->examsessions()->whereHas('candidates', function ($query) use ($candidate) {
                                $query->where('candidate_id', $candidate->id);
                            })->exists();
                        });
                        return $allModulesHaveExamSession ? 'heroicon-o-check-circle' : 'heroicon-o-clock';
                    })
                    ->tooltip(function (Candidate $candidate) {
                        $modules = $candidate->modules;
                        $modulesWithoutExamSession = $modules->reject(function ($module) use ($candidate) {
                            return $module->examsessions()->whereHas('candidates', function ($query) use ($candidate) {
                                $query->where('candidate_id', $candidate->id);
                            })->exists();
                        });
                        $moduleNames = $modulesWithoutExamSession->pluck('name')->toArray();
                        return $moduleNames == [] ? '' : 'Modules missing to be assigned: ' . implode(', ', $moduleNames);
                    })
                    ->color(function (Candidate $candidate) {
                        $modules = $candidate->modules;
                        $allModulesHaveExamSession = $modules->every(function ($module) use ($candidate) {
                            return $module->examsessions()->whereHas('candidates', function ($query) use ($candidate) {
                                $query->where('candidate_id', $candidate->id);
                            })->exists();
                        });
                        return $allModulesHaveExamSession ? 'success' : 'warning';
                    }) */,

                //Student
                TextColumn::make('student.names')
                    ->label('Names')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('student.last_name')
                    ->label('Last Name')
                    ->sortable()
                    ->searchable(),

                //Exam
                TextColumn::make('exam.session_name')
                    ->label('Session Name'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('qr-code')
                    ->label('QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->url(fn (Candidate $candidate) => route('candidate.view', ['id' => $candidate->id]), shouldOpenInNewTab: true),
                Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document')
                    ->url(fn (Candidate $candidate) => route('candidate.download-pdf', ['id' => $candidate->id]), shouldOpenInNewTab: true),
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('export-excel')
                        ->label('Download as Excel')
                        ->icon('heroicon-o-document')
                        ->action(fn (Collection $records) => (new CandidateByIdExport($records->pluck('id')))->download('candidates.xlsx')),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCandidates::route('/'),
            'create' => Pages\CreateCandidate::route('/create'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('student', fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()));
    }
}

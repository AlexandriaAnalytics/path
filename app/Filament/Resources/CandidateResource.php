<?php

namespace App\Filament\Resources;

use App\Enums\TypeOfCertificate;
use App\Enums\UserStatus;
use App\Exports\CandidateByIdExport;
use App\Filament\Admin\Resources\CandidateResource as AdminCandidateResource;
use App\Filament\Resources\CandidateResource\Pages;
use App\Models\Candidate;
use App\Models\Change;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Closure;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportConsoleCommands\Commands\Upgrade\ChangeTestAssertionMethods;

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
                            ->getOptionLabelFromRecordUsing(fn (Student $record) => "{$record->name} {$record->surname}")
                            ->searchable()
                            ->preload()
                            ->required()
                    ]),
                ...AdminCandidateResource::getExamFields(),
                Select::make('type_of_certificate')
                    ->native(false)
                    ->options(TypeOfCertificate::class)
                    ->enum(TypeOfCertificate::class)
                    ->required(),
                TextInput::make('granted_discount')
                    ->label('Discount')
                    ->postfix('%')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100)
                    ->hint(fn () => 'Available discount: ' . Filament::getTenant()->remaining_discount . '%')
                    ->rules([
                        fn (): Closure => function (string $attribute, $value, Closure $fail) {
                            if ($value > Filament::getTenant()->remaining_discount) {
                                $fail('The institute does not have enough discount to grant.');
                            }
                        },
                    ]),

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

                //Student
                TextColumn::make('student.name')
                    ->label('Names')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('student.surname')
                    ->label('Last Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('level.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('modules.name')
                    ->badge(),
                IconColumn::make('modules')
                    ->label('Exam session')
                    ->icon(function (Candidate $candidate) {
                        $modules = $candidate->modules;
                        $allModulesHaveExamSession = $modules->every(function ($module) use ($candidate) {
                            return $module->candidateExams()->whereHas('candidate', function ($query) use ($candidate) {
                                $query->where('candidate_id', $candidate->id);
                            })->exists();
                        });
                        return $allModulesHaveExamSession ? 'heroicon-o-check-circle' : 'heroicon-o-clock';
                    })
                    ->tooltip(function (Candidate $candidate) {
                        $modules = $candidate->modules;
                        $modulesWithoutExamSession = $modules->reject(function ($module) use ($candidate) {
                            return $module->candidateExams()->whereHas('candidate', function ($query) use ($candidate) {
                                $query->where('candidate_id', $candidate->id);
                            })->exists();
                        });
                        $moduleNames = $modulesWithoutExamSession->pluck('name')->toArray();
                        return $moduleNames == [] ? '' : 'Modules missing to be assigned: ' . implode(', ', $moduleNames);
                    })
                    ->color(function (Candidate $candidate) {
                        $modules = $candidate->modules;
                        $allModulesHaveExamSession = $modules->every(function ($module) use ($candidate) {
                            return $module->candidateExams()->whereHas('candidate', function ($query) use ($candidate) {
                                $query->where('candidate_id', $candidate->id);
                            })->exists();
                        });
                        return $allModulesHaveExamSession ? 'success' : 'warning';
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('qr-code')
                        ->label('QR Code')
                        ->icon('heroicon-o-qr-code')
                        ->url(fn (Candidate $candidate) => route('candidate.view', ['id' => $candidate->id]), shouldOpenInNewTab: true),
                    Action::make('pdf')
                        ->label('PDF')
                        ->icon('heroicon-o-document')
                        ->url(fn (Candidate $candidate) => route('candidate.download-pdf', ['id' => $candidate->id]), shouldOpenInNewTab: true),
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn (Candidate $candidate) => $candidate->status !== 'paid'),
                    Action::make('request changes')
                        ->visible(fn (Candidate $candidate) => $candidate->status === 'paid')
                        ->icon('heroicon-o-arrows-right-left')
                        ->form([
                            Textarea::make('changes')
                        ])
                        ->action(function (array $data, Candidate $candidate) {
                            $change = new Change();
                            $change->description = $data['changes'];
                            $change->status = 0;
                            $change->candidate_id = $candidate->id;
                            $change->user_id = Auth::user()->id;
                            $change->save();
                        }),
                    DeleteAction::make(),
                ])
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
            'view' => Pages\ViewCandidate::route('/{record}'),
            'edit' => Pages\EditCandidate::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('student', fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()));
    }
}

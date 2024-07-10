<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CandidateRecordResource\Pages;
use App\Filament\Admin\Resources\CandidateRecordResource\RelationManagers;
use App\Models\Candidate;
use App\Models\CandidateRecord;
use App\Models\Section;
use App\Models\StatusActivity;
use App\Models\TypeOfTraining;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class CandidateRecordResource extends Resource
{
    protected static ?string $model = CandidateRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Corporate';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('candidate_id')
                    ->label('Candidate')
                    ->options(Candidate::query()->get()->mapWithKeys(fn (Candidate $candidate) => [
                        $candidate->id => "{$candidate->id} - {$candidate->student->name} {$candidate->student->surname}"
                    ]))
                    ->required(),
                Select::make('section_id')
                    ->label('Section')
                    ->options(Section::all()->pluck('name', 'id'))
                    ->required(),
                Select::make('status_activity_id')
                    ->label('Status activity')
                    ->options(StatusActivity::all()->pluck('name', 'id'))
                    ->required(),
                Select::make('attendance')
                    ->options([
                        '-' => '-',
                        'Present' => 'Present',
                        'Absent' => 'Absent'
                    ])
                    ->required(),
                Select::make('result')
                    ->options([
                        'Certified' => 'Certified',
                        'To be reviewed' => 'To be reviewed'
                    ]),
                Textarea::make('comments'),
                TextInput::make('type_of_training_id')
                    ->hidden(),
                TextInput::make('can_access')
                    ->hidden()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Group::make('section.student')
                    ->groupQueryUsing(fn (Builder $query) => $query->groupBy('section'))
                    ->collapsible(),
            ])
            ->defaultGroup('section.name')
            ->columns([
                TextColumn::make('candidate.student.name')
                    ->formatStateUsing(fn (CandidateRecord $record) => $record->candidate->student->name . ' ' . $record->candidate->student->surname)
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('section_id')
                    ->label('Section')
                    ->formatStateUsing(function ($state) {
                        return Section::find($state)->name;
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('type_of_training_id')
                    ->label('Type of training')
                    ->formatStateUsing(function ($state) {
                        return TypeOfTraining::find($state)->name;
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('result')
                    ->default('-')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('status_activity_id')
                    ->label('Status section')
                    ->formatStateUsing(function ($state) {
                        return StatusActivity::find($state)->name;
                    })
                    ->badge()
                    ->color(function ($state) {
                        return Color::hex(StatusActivity::find($state)->color);
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('comments')
                    ->sortable()
                    ->searchable()
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('attendance')
                    ->formatStateUsing(function (CandidateRecord $record) {
                        return $record->attendanceAttribute;
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('help')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                TernaryFilter::make('ask_for_help')
                    ->label('Ask for help')
                    ->trueLabel('Pending')
                    ->falseLabel('No ask')
                    ->queries(
                        true: fn (Builder $query) => $query->where('help', 'Pending'),
                        false: fn (Builder $query) => $query->where('help', '!=', 'Pending')->orWhereNull('help'),
                    )
                    ->native(false),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Action::make('download-pdf')
                        ->visible(function (CandidateRecord $record) {
                            return $record->result == null ? false : true;
                        })
                        ->label('Download PDF')
                        ->icon('heroicon-o-document')
                        ->action(function (CandidateRecord $record) {
                            //dd($record->trainee->answers[3]->question->multipleChoices[0]->answers);
                            try {
                                $filename = "{$record->candidate->student->name} {$record->candidate->student->surname} - {$record->section->name}.pdf";
                                $pdfPath = storage_path('app/temp_pdfs') . '/' . $filename;

                                // Ensure the temporary directory exists and has write permissions
                                if (!File::exists(storage_path('app/temp_pdfs'))) {
                                    File::makeDirectory(storage_path('app/temp_pdfs'), 0755, true); // Create directory with appropriate permissions
                                }
                                Pdf::loadView('pdf.candidaterecord', ['record' => $record])
                                    ->save($pdfPath);
                                return response()->download($pdfPath, $filename, [
                                    'Content-Type' => 'application/pdf',
                                ]);
                            } catch (\Exception $e) {
                                return response()->json(['error' => 'PDF generation or download failed'], 500);
                            }
                        }),
                    Action::make('refresh-status')
                        ->label('Update access')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Checkbox::make('can_access')
                                ->default(function (CandidateRecord $record) {
                                    if ($record->can_access == 'can') {
                                        return true;
                                    }
                                    return false;
                                })
                        ])
                        ->action(function (array $data, CandidateRecord $record) {
                            $record->can_access = $data['can_access'] ? 'can' : 'cant';
                            $record->save();
                        }),

                    Action::make('resolve-request-for-help')
                        ->label('Resolve request for help')
                        ->icon('heroicon-o-hand-raised')
                        ->action(function (CandidateRecord $record) {
                            $record->help = 'Solved';
                            $record->save();
                            Notification::make()
                                ->title('Query resolved successfully')
                                ->success()
                                ->send();
                        })
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    BulkAction::make('refresh-status')
                        ->label('Refresh status')
                        ->icon('heroicon-o-arrow-path')
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->result = null;
                                $record->save();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('update-status')
                        ->label('Update status')
                        ->icon('heroicon-o-arrows-right-left')
                        ->form([
                            Select::make('status_activity_id')
                                ->label('Status section')
                                ->options(StatusActivity::all()->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function (Collection $records, $data) {
                            foreach ($records as $record) {
                                $record->status_activity_id = $data['status_activity_id'];
                                $record->save();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),

                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListCandidateRecords::route('/'),
            'create' => Pages\CreateCandidateRecord::route('/create'),
            'edit' => Pages\EditCandidateRecord::route('/{record}/edit'),
        ];
    }
}

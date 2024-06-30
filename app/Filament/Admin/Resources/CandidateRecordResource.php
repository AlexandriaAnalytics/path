<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CandidateRecordResource\Pages;
use App\Filament\Admin\Resources\CandidateRecordResource\RelationManagers;
use App\Models\Candidate;
use App\Models\CandidateRecord;
use App\Models\Section;
use App\Models\StatusActivity;
use App\Models\TypeOfTraining;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

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
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),

                    Action::make('refresh-status')
                        ->label('Refresh access')
                        ->icon('heroicon-o-arrow-path')
                        ->action(function () {
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

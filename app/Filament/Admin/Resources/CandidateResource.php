<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CandidateResource\Pages;
use App\Filament\Admin\Resources\CandidateResource\RelationManagers;
use App\Models\Candidate;
use App\Models\Exam;
use App\Models\Institute;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;
    protected static ?string $navigationGroup = 'Corporate';
    protected static ?string $navigationIcon = 'heroicon-m-academic-cap';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Student')
                    ->schema([
                        Select::make('institute_id')
                            ->label('Institute')
                            ->placeholder('Select an institute')
                            ->options(Institute::all()->pluck('name', 'id'))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('student_id', null)),
                        Select::make('student_id')
                            ->label('Student')
                            ->placeholder('Select a student')
                            ->searchable()
                            ->live()
                            ->options(function (callable $get) {
                                $instituteId = $get('institute_id');

                                if (!$instituteId) {
                                    return [];
                                }

                                return Student::query()
                                    ->whereInstituteId($instituteId)
                                    ->pluck('first_name', 'id');
                            })
                    ]),
                Fieldset::make('Exam')
                    ->schema([
                        Select::make('exam_id')
                            ->label('Exam')
                            ->placeholder('Select an exam')
                            ->options(Exam::all()->pluck('session_name', 'id'))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('modules', null)),
                        Select::make('modules')
                            ->multiple()
                            ->required()
                            ->live()
                            ->options(function (callable $get) {
                                $examId = $get('exam_id');

                                if (!$examId) {
                                    return [];
                                }

                                return Exam::query()
                                    ->whereId($examId)
                                    ->first()
                                    ->modules
                                    ->flatMap(fn ($module) => [$module['type']->value => "{$module['type']->getLabel()} (\${$module['price']})"]);
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ColumnGroup::make('Candidate', [
                    TextColumn::make('id')
                        ->label('Candidate No.')
                        ->sortable()
                        ->searchable()
                        ->numeric(),
                    // TextColumn::make('payment_status')
                    //     ->label('Payment Status')
                    //     ->badge(),
                ]),
                ColumnGroup::make('Student', [
                    TextColumn::make('student.national_id')
                        ->label('National ID')
                        ->searchable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('student.first_name')
                        ->label('First Name')
                        ->sortable()
                        ->searchable(),
                    TextColumn::make('student.last_name')
                        ->label('Last Name')
                        ->sortable()
                        ->searchable(),
                ]),
                ColumnGroup::make('Institute', [
                    TextColumn::make('student.institute.name')
                        ->label('Institute Name')
                        ->sortable()
                        ->searchable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ]),
                ColumnGroup::make('Exam', [
                    TextColumn::make('exam.session_name')
                        ->label('Session Name'),
                ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('institute_id')
                    ->label('Institute')
                    ->relationship('student.institute', 'name')
                    ->searchable()
                    ->multiple()
                    ->preload(),
                Tables\Filters\SelectFilter::make('exam_id')
                    ->label('Exam')
                    ->relationship('exam', 'session_name')
                    ->searchable()
                    ->multiple()
                    ->preload(),
            ]);
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
            // 'create' => Pages\CreateCandidate::route('/create'),
            'view' => Pages\ViewCandidate::route('/{record}'),
            // 'edit' => Pages\EditCandidate::route('/{record}/edit'),
        ];
    }
}

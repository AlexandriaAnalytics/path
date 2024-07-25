<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ExamResource\Pages;
use App\Filament\Admin\Resources\ExamResource\RelationManagers;
use App\Models\Exam;
use App\Models\InstituteType;
use App\Models\Level;
use App\Models\Module;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static ?string $navigationGroup = 'Corporate';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $modelLabel = 'Exam session';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Details')
                    ->columns([
                        'sm' => 1,
                        'xl' => 12
                    ])
                    ->schema([
                        TextInput::make('session_id')
                            ->required()
                            ->autofocus()
                            ->maxLength(255)
                            ->columnSpan(4),
                        Forms\Components\TextInput::make('session_name')
                            ->required()
                            ->autofocus()
                            ->maxLength(255)
                            ->columnSpan(4),
                        Forms\Components\TextInput::make('location')
                            ->required()
                            ->autofocus()
                            ->columnSpan(4),
                        Select::make('institute_type_id')
                            ->label('Membership')
                            ->options(InstituteType::all()->pluck('name', 'id'))
                            ->required()
                            ->autofocus()
                            ->columnSpan(4),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'in review' => 'In review',
                                'closed' => 'Closed',
                                'finished' => 'Finished',
                                'archived' => 'Archived'

                            ])
                            ->required()
                            ->autofocus()
                            ->columnSpan(4),
                        Forms\Components\TextInput::make('maximum_number_of_students')
                            ->numeric()
                            ->required()
                            ->label('Maximum number of candidates')
                            ->columnSpan(4),
                        TextInput::make('installments')
                            ->label('Maximum number of installments')
                            ->numeric()
                            ->required()
                            ->columnSpan(4),
                        Select::make('examiners')
                            ->options(function () {
                                return User::select('users.name')
                                    ->join('trainees', 'users.id', '=', 'trainees.user_id')
                                    ->join('trainee_training', 'trainees.id', '=', 'trainee_training.trainee_id')
                                    ->join('type_of_trainings', 'trainee_training.type_of_training_id', '=', 'type_of_trainings.id')
                                    ->where('type_of_trainings.name', 'Examiners')->distinct()->pluck('name');
                            })
                            ->columnSpan(4),
                        Select::make('supervisors')
                            ->options(function () {
                                return User::select('users.name')
                                    ->join('trainees', 'users.id', '=', 'trainees.user_id')
                                    ->join('trainee_training', 'trainees.id', '=', 'trainee_training.trainee_id')
                                    ->join('type_of_trainings', 'trainee_training.type_of_training_id', '=', 'type_of_trainings.id')
                                    ->where('type_of_trainings.name', 'Supervisor')->distinct()->pluck('name');
                            })
                            ->columnSpan(4),

                        Forms\Components\Select::make('levels')
                            ->columnSpan(6)
                            ->relationship(titleAttribute: 'name')
                            ->native(false)
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->suffixAction(
                                Action::make('select-all')
                                    ->icon('heroicon-o-clipboard-document-list')
                                    ->label('Select All')
                                    ->tooltip('Select all levels')
                                    ->action(function (Set $set) {
                                        $set('levels', Level::all()->pluck('id'));
                                    }),
                            )->label('Exam'),
                        Repeater::make('modules')
                            ->columnSpanFull()
                            ->columns(3)
                            ->schema([
                                Forms\Components\Select::make('modules')
                                    ->relationship(name: 'modules', titleAttribute: 'name')
                                    ->native(false)
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->suffixAction(
                                        Action::make('select-all')
                                            ->icon('heroicon-o-clipboard-document-list')
                                            ->label('Select All')
                                            ->tooltip('Select all modules')
                                            ->action(function (Set $set) {
                                                $set('modules', Module::all()->pluck('id'));
                                            }),
                                    ),
                                Forms\Components\Select::make('type')
                                    ->options(\App\Enums\ExamType::class)
                                    ->native(false)
                                    ->required()
                                    ->live()
                                    ->enum(\App\Enums\ExamType::class),
                                DateTimePicker::make('scheduled_date')
                                    ->required()
                                    ->seconds(false)
                            ]),
                        Forms\Components\RichEditor::make('comments')
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('session_name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('scheduled_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('modules.name')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('maximum_number_of_students')
                    ->label('Max. candidates')
                    ->prefix(function ($record) {
                        return $record->candidates->unique('id')->count() . ' / ';
                    })
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('location')
                    ->default('-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->deselectRecordsAfterCompletion(),
                    Tables\Actions\ForceDeleteBulkAction::make()->deselectRecordsAfterCompletion(),
                    Tables\Actions\RestoreBulkAction::make()->deselectRecordsAfterCompletion(),
                    BulkAction::make('extendDuration')
                        ->icon('heroicon-o-clock')
                        ->form([
                            TextInput::make('duration')
                                ->numeric()
                                ->helperText('Extra minutes')
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function ($record) use ($data) {
                                $record->update([
                                    'duration' => $record->duration + $data['duration'],
                                ]);
                            });


                            Notification::make()
                                ->title('Exam session extended successfully')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ExamResource\RelationManagers\CandidatesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExams::route('/'),
            'create' => Pages\CreateExam::route('/create'),
            'view' => Pages\ViewExam::route('/{record}'),
            'edit' => Pages\EditExam::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ExamResource\Pages;
use App\Filament\Admin\Resources\ExamResource\RelationManagers;
use App\Models\Exam;
use App\Models\ExamModule;
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
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
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
                            ->label('Session ID')
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
                            ->multiple()
                            ->relationship('examiners', 'name')
                            ->options(function () {
                                return User::whereHas('trainees', function ($query) {
                                    $query->whereHas('typeOfTraining', function ($query) {
                                        $query->where('name', 'Examiners');
                                    });
                                })
                                    ->distinct()
                                    ->get()
                                    ->pluck('name', 'id');
                            })
                            ->preload()
                            ->columnSpan(4),
                        Select::make('supervisors')
                            ->multiple()
                            ->relationship('supervisors', 'name')
                            ->options(function () {
                                return User::whereHas('trainees', function ($query) {
                                    $query->whereHas('typeOfTraining', function ($query) {
                                        $query->where('name', 'Supervisor');
                                    });
                                })
                                    ->distinct()
                                    ->get()
                                    ->pluck('name', 'id');
                            })
                            ->preload()
                            ->columnSpan(4),

                        Forms\Components\Select::make('levels')
                            ->columnSpan(6)
                            ->relationship(titleAttribute: 'name')
                            ->native(false)
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->suffixAction(
                                Action::make('select-all')
                                    ->icon('heroicon-o-clipboard-document-list')
                                    ->label('Select All')
                                    ->tooltip('Select all levels')
                                    ->action(function (Set $set) {
                                        $set('levels', Level::all()->pluck('id'));
                                    }),
                            )->label('Exam'),
                        Repeater::make('examModules')
                            ->label('Modules')
                            ->columnSpanFull()
                            ->columns(3)
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('module_id')
                                    ->relationship('module', 'name')
                                    ->native(false)
                                    ->searchable()
                                    ->required()
                                    ->preload(),
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
                Tables\Columns\TextColumn::make('session_id')
                    ->label('Session ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active' => Color::hex('#83A982'),
                        'in review' => Color::hex('#D4AC71'),
                        'closed' => Color::hex('#C94F40'),
                        'finished' => Color::hex('#000000'),
                        'archived' => Color::hex('#98A4A2')
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('session_name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('levels.name')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('modules')
                    ->formatStateUsing(function (Exam $record) {
                        $modules = $record->examModules;
                        $return = "";
                        foreach ($modules as $module) {
                            $return = $return . $module->module->name . "<br>" . $module->type . "<br>" . $module->scheduled_date . "<br><br>";
                        }
                        return $return;
                    })
                    ->html()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('location')->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('installments')
                    ->label('Maximum number of installments')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('instituteType.name')
                    ->label('Membership')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('maximum_number_of_students')
                    ->label('Maximum number of candidates')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('examiners.name')
                    ->sortable()
                    ->wrap()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('supervisors.name')
                    ->sortable()
                    ->searchable()
                    ->wrap()
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

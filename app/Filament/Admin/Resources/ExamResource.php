<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ExamResource\Pages;
use App\Filament\Admin\Resources\ExamResource\RelationManagers;
use App\Models\Exam;
use App\Models\Level;
use App\Models\Module;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
                        'xl' => 3
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('session_name')
                            ->required()
                            ->autofocus()
                            ->maxLength(255)
                            ->columnSpan([
                                'sm' => 1,
                                'xl' => 2,
                            ]),
                        Forms\Components\TextInput::make('maximum_number_of_students')
                            ->numeric()
                            ->required()
                            ->label('Maximum number of candidates'),
                        Forms\Components\DateTimePicker::make('scheduled_date')
                            ->seconds(false)
                            ->required(),
                        Forms\Components\DateTimePicker::make('payment_deadline')
                            ->seconds(false)
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->options(\App\Enums\ExamType::class)
                            ->native(false)
                            ->required()
                            ->enum(\App\Enums\ExamType::class),
                        Forms\Components\TextInput::make('location')
                            ->default('-')
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('comments')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Exams and modules')
                    ->collapsible()
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('levels')
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
                            )
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

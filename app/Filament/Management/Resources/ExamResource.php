<?php

namespace App\Filament\Management\Resources;

use App\Filament\Admin\Resources\ExamResource as AdminExamResource;
use App\Filament\Management\Resources\ExamResource\Pages;
use App\Filament\Management\Resources\ExamResource\RelationManagers;
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

    protected static ?string $navigationGroup = 'Exam management';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Exam sessions';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $modelLabel = 'Exam session';

    protected static ?string $pluralModelLabel = 'Exam sessions';

    protected static ?int $navigationSort = 2;

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
                            ->label('Maximum number of candidates'),
                        Forms\Components\DateTimePicker::make('scheduled_date')
                            ->seconds(false)
                            ->minutesStep(5)
                            ->required()
                            ->minDate(now()),
                        Forms\Components\Select::make('type')
                            ->options(\App\Enums\ExamType::class)
                            ->native(false)
                            ->required()
                            ->enum(\App\Enums\ExamType::class),
                        Forms\Components\TextInput::make('location')
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
                            ->label('Exam'),
                        Forms\Components\Select::make('modules')
                            ->relationship(name: 'modules', titleAttribute: 'name')
                            ->native(false)
                            ->multiple()
                            ->searchable()
                            ->preload()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return AdminExamResource::table($table)
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CandidatesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExams::route('/'),
            'view' => Pages\ViewExam::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return Exam::query()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

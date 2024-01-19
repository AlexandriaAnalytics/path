<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ExamResource\Pages;
use App\Filament\Admin\Resources\ExamResource\RelationManagers;
use App\Models\Exam;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static ?string $navigationGroup = 'Exam Management';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('exam_session_name')
                            ->required()
                            ->autofocus()
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('scheduled_date')
                            ->native(false)
                            ->seconds(false)
                            ->minutesStep(5)
                            ->required()
                            ->minDate(now()),
                        Forms\Components\Select::make('type')
                            ->options(\App\Enums\ExamType::class)
                            ->native(false)
                            ->required()
                            ->enum(\App\Enums\ExamType::class),
                        Forms\Components\TextInput::make('maximum_number_of_students')
                            ->numeric(),
                        Forms\Components\RichEditor::make('comments')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Modules and Levels')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('levels')
                            ->relationship(titleAttribute: 'name')
                            ->native(false)
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Repeater::make('modules')
                            ->addActionLabel('Add module')
                            ->columns(2)
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->options(\App\Enums\Module::class)
                                    ->native(false)
                                    ->required()
                                    ->distinct()
                                    ->enum(\App\Enums\Module::class),
                                Forms\Components\TextInput::make('price')
                                    ->prefix('$')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('exam_session_name'),
                Tables\Columns\TextColumn::make('scheduled_date'),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('maximum_number_of_students'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
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

<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ExamSessionResource\Pages;
use App\Filament\Admin\Resources\ExamSessionResource\RelationManagers;
use App\Models\ExamSession;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExamSessionResource extends Resource
{
    protected static ?string $model = ExamSession::class;

    protected static ?string $navigationGroup = 'Exam Management';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('session_name')
                    ->required()
                    ->autofocus()
                    ->maxLength(255),
                DateTimePicker::make('scheduled_date')
                    ->native(false)
                    ->seconds(false)
                    ->minutesStep(5)
                    ->required()
                    ->minDate(now()),
                Select::make('module_id')
                    ->label('Module')
                    ->relationship('module', 'name')
                    ->searchable()
                    ->native(false)
                    ->preload()
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListExamSessions::route('/'),
            'create' => Pages\CreateExamSession::route('/create'),
            'edit' => Pages\EditExamSession::route('/{record}/edit'),
        ];
    }
}

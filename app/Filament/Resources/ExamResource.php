<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamResource\Pages;
use App\Filament\Resources\ExamResource\RelationManagers;
use App\Models\Exam;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use App\Models\Level;

class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('exam_session_name'),
                Forms\Components\DatePicker::make('scheduled_date'),
                Forms\Components\Select::make('type')
                    ->options([
                        'online' => 'Online',
                        'on-site' => 'On-site'
                    ]),
                Forms\Components\TextInput::make('maximum_number_of_candidates')
                    ->numeric(),
                Forms\Components\CheckboxList::make('levels')
                    ->relationship(titleAttribute: 'name'),
                Forms\Components\CheckboxList::make('skills')
                    ->relationship(titleAttribute: 'skill_name'),
                Forms\Components\Textarea::make('comments')
                    ->columnSpanFull(),
                Hidden::make('institute_id')
                    ->default(Auth::user()->institute_id)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('exam_session_name'),
                Tables\Columns\TextColumn::make('scheduled_date'),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('maximum_number_of_candidates'),
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

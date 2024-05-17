<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ExaminerActivityResource\Pages;
use App\Filament\Admin\Resources\ExaminerActivityResource\RelationManagers;
use App\Models\ExaminerActivity;
use App\Models\Performance;
use App\Models\Section;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExaminerActivityResource extends Resource
{
    protected static ?string $model = ExaminerActivity::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Training';
    protected static ?string $modelLabel = 'Examiner activity';
    protected static bool $hasTitleCaseModelLabel = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('section_id')
                    ->label('Section')
                    ->options(Section::all()->pluck('name', 'id'))
                    ->required(),
                Repeater::make('questions')
                    ->schema([
                        TextInput::make('question')
                            ->required(),
                        TextInput::make('description'),
                        Toggle::make('open_or_close'),
                        Repeater::make('Answers')
                            ->schema([
                                TextInput::make('answer'),
                                Select::make('performance')
                                    ->options(Performance::all()->pluck('answer', 'id'))
                            ])->columns(2),
                        FileUpload::make('multimedia')
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListExaminerActivities::route('/'),
            'create' => Pages\CreateExaminerActivity::route('/create'),
            'edit' => Pages\EditExaminerActivity::route('/{record}/edit'),
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

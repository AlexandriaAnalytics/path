<?php

namespace App\Filament\Resources;

use App\Enums\Country;
use App\Enums\UserStatus;
use App\Exports\StudentExport;
use App\Filament\Admin\Resources\StudentResource as AdminStudentResource;
use App\Filament\Resources\StudentResource\Pages;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Exam Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name'),
                Forms\Components\TextInput::make('surname'),
                Forms\Components\Select::make('country_id')
                    ->label('Country of residence')
                    ->relationship('region', 'name')
                    ->preload()
                    ->searchable(),
                Forms\Components\TextInput::make('cbu'),
                Forms\Components\DatePicker::make('birth_date')
                    ->label('Date of birth')
                    ->native(false)
                    ->placeholder('dd/mm/yyyy'),
                Forms\Components\RichEditor::make('personal_educational_needs')
                    ->columnSpanFull()

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ...AdminStudentResource::getStudentColumns(),
                ...AdminStudentResource::getMetadataColumns(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('export-excel')
                        ->label('Download as Excel')
                        ->icon('heroicon-o-document')
                        ->action(fn (Collection $records) => (new StudentExport($records->pluck('id')))->download('students.xlsx')),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /* public static function getRelations(): array
    {
        return [
            StudentResource\RelationManagers\ExamsRelationManager::class,
        ];
    } */

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
            'view' => Pages\ViewStudent::route('/{record}'),
        ];
    }
}

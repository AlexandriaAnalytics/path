<?php

namespace App\Filament\Admin\Resources;

use App\Enums\Module;
use App\Exports\StudentExport;
use App\Models\Country;
use App\Models\Student;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationGroup = 'Corporate';

    protected static ?string $navigationIcon = 'heroicon-m-user-group';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('Personal Information')
                    ->columns(2)
                    ->schema([
                        Components\TextInput::make('name')
                            ->required()
                            ->placeholder('John'),
                        Components\TextInput::make('surname')
                            ->label('Last Name')
                            ->required()
                            ->placeholder('Doe'),
                        Components\Select::make('institute_id')
                            ->label('Institute')
                            ->relationship('institute', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required(),
                        Components\DatePicker::make('birth_date')
                            ->label('Date of birth')
                            ->native(false)
                            ->placeholder('dd/mm/yyyy')
                            ->required(),
                    ]),
                Components\Section::make('Country of residence')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Components\Select::make('country_id')
                            ->label('Region')
                            ->relationship('region', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),
                    ]),
                Components\RichEditor::make('personal_educational_needs')
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ...static::getStudentColumns(),
                TextColumn::make('institute.name')
                    ->searchable()
                    ->sortable(),
                ...static::getMetadataColumns(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('institute_id')
                    ->label('Institute')
                    ->relationship('institute', 'name')
                    ->native(false)
                    ->searchable()
                    ->multiple()
                    ->preload(),
                Tables\Filters\QueryBuilder::make()
                    ->constraints([
                        DateConstraint::make('created_at')->label('Created on')
                            ->label('Registered at'),
                    ]),
            ])
            ->filtersFormWidth(MaxWidth::TwoExtraLarge)
            ->actions([
                Tables\Actions\EditAction::make(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => StudentResource\Pages\ListStudents::route('/'),
            // 'create' => StudentResource\Pages\CreateStudent::route('/create'),
            'edit' => StudentResource\Pages\EditStudent::route('/{record}/edit'),
            'view' => StudentResource\Pages\ViewStudent::route('/{record}'),
        ];
    }

    public static function getStudentColumns(): array
    {
        return [
            TextColumn::make('name')
                ->searchable()
                ->sortable(),
            TextColumn::make('surname')
                ->searchable()
                ->sortable(),
            TextColumn::make('region.name')
                ->label('Region')
                ->searchable()
                ->badge()
                ->sortable(),

            TextColumn::make('birth_date')
                ->label('Date of birth')
                ->date()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function getMetadataColumns(): array
    {
        return [
            TextColumn::make('created_at')->label('Created on')
                ->date()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')->label('Updated on')
                ->date()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }
}

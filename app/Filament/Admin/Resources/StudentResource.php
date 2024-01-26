<?php

namespace App\Filament\Admin\Resources;

use App\Enums\Country;
use App\Enums\Module;
use App\Models\Student;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationGroup = 'Corporate';

    protected static ?string $navigationIcon = 'heroicon-m-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('Personal Information')
                    ->columns(2)
                    ->schema([
                        Components\TextInput::make('first_name')
                            ->label('First Name')
                            ->required()
                            ->placeholder('John'),
                        Components\TextInput::make('last_name')
                            ->label('Last Name')
                            ->required()
                            ->placeholder('Doe'),
                        Components\Select::make('institute_id')
                            ->label('Institute')
                            ->relationship('institute', 'name')
                            ->searchable()
                            ->native(false)
                            ->required(),
                        Components\TextInput::make('phone')
                            ->autofocus()
                            ->placeholder('0118-999-881-999-119-725-3'),
                        Components\TextInput::make('national_id')
                            ->label('National ID')
                            ->placeholder('20-12345678-9')
                            ->mask('99-99999999-9')
                            ->autofocus()
                            ->required(),
                        Components\TextInput::make('birth_date')
                            ->autofocus()
                            ->type('date')
                            ->required(),
                    ]),
                Components\Section::make('Address')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Components\Select::make('country')
                            ->label('Country')
                            ->searchable()
                            ->required()
                            ->options(Country::class)
                            ->enum(Country::class)
                            ->native(false),
                        Components\TextInput::make('address')
                            ->autofocus()
                            ->placeholder('Evergreen Terrace 742'),
                    ]),
                Components\Section::make('Additional Information')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Components\TextInput::make('cbu')
                            ->label('CBU')
                            ->autofocus()
                            ->placeholder('1234567890123456789012')
                            ->required(),
                        Components\Select::make('status')
                            ->label('Status')
                            ->options(['active' => 'active', 'inactive' => 'inactive'])
                            ->placeholder('Select Status')
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ...static::getStudentColumns(),
                ColumnGroup::make('Institute', [
                    TextColumn::make('institute.name')
                        ->searchable()
                        ->sortable(),
                ]),
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
                        DateConstraint::make('created_at')
                            ->label('Registered at'),
                    ]),
            ])
            ->filtersFormWidth(MaxWidth::TwoExtraLarge)
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
            'index' => StudentResource\Pages\ListStudents::route('/'),
            // 'create' => StudentResource\Pages\CreateStudent::route('/create'),
            'edit' => StudentResource\Pages\EditStudent::route('/{record}/edit'),
            'view' => StudentResource\Pages\ViewStudent::route('/{record}'),
        ];
    }

    public static function getStudentColumns(): array
    {
        return [
            ColumnGroup::make('Personal Information', [
                TextColumn::make('national_id')
                    ->label('National ID')
                    ->searchable(isIndividual: true)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('birth_date')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]),
        ];
    }

    public static function getMetadataColumns(): array
    {
        return [
            ColumnGroup::make('Metadata', [
                TextColumn::make('created_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]),
        ];
    }
}

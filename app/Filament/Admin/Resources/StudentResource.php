<?php

namespace App\Filament\Admin\Resources;

use App\Enums\Country;
use App\Filament\Admin\Resources\StudentResource\Pages\CreateStudent;
use App\Filament\Admin\Resources\StudentResource\Pages\ListStudents;
use App\Filament\Admin\Resources\StudentResource\RelationManagers;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;
    protected static ?string $navigationGroup = 'Corporate';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('first_name')
                    ->autofocus()
                    ->required()
                    ->placeholder('first name'),
                TextInput::make('last_name')
                    ->autofocus()
                    ->required()
                    ->placeholder('last name'),

                Select::make('country')
                    ->options(Country::values())
                    ->displayUsingLabels()
                    ->placeholder('Select Country')
                    ->required(),

                TextInput::make('address')
                    ->autofocus()
                    ->placeholder('address'),

                TextInput::make('phone')
                    ->autofocus()
                    ->placeholder('phone'),

                TextInput::make('cbu')
                    ->autofocus()
                    ->placeholder('cbu')
                    ->required(),

                TextInput::make('cuil')
                    ->autofocus()
                    ->placeholder('cuil')
                    ->required(),

                TextInput::make('birth_date')
                    ->autofocus()
                    ->type('date')
                    ->required(),

                Select::make('status')
                    ->options(['active' => 'active', 'inactive' => 'inactive'])
                    ->displayUsingLabels()
                    ->placeholder('Select Status')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('address'),
                Tables\Columns\TextColumn::make('cbu'),
                Tables\Columns\TextColumn::make('cuil'),
                Tables\Columns\TextColumn::make('birth_date'),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->sortable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['active' => 'active', 'inactive' => 'inactive'])
                    ->label('Status')
                    ->placeholder('Select Status'),
                Tables\Filters\SelectFilter::make('institute_id')
                    ->relationship('institute', 'name')
                    ->label('Intitute')
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
            'index' => ListStudents::route('/'),
            //            'create' => CreateStudent::route('/create'),
            //'edit' => EditSt//Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}

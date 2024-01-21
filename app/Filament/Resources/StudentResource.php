<?php

namespace App\Filament\Resources;

use App\Enums\Country;
use App\Enums\UserStatus;
use App\Filament\Resources\StudentResource\Pages;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Exam Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name'),
                Forms\Components\TextInput::make('last_name'),
                Forms\Components\TextInput::make('slug'),
                Forms\Components\Select::make('country')
                    ->label('Country')
                    ->options(Country::class)
                    ->enum(Country::class)
                    ->searchable(),
                Forms\Components\TextInput::make('address'),
                Forms\Components\TextInput::make('phone'),
                Forms\Components\TextInput::make('cbu'),
                Forms\Components\TextInput::make('cuil'),
                Forms\Components\DatePicker::make('birth_date'),
                Forms\Components\Select::make('status')
                    ->options(UserStatus::values()),
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
                Tables\Columns\TextColumn::make('address')->sortable(),
                Tables\Columns\TextColumn::make('cbu')->sortable(),
                Tables\Columns\TextColumn::make('cuil')->sortable(),
                Tables\Columns\TextColumn::make('birth_date')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Enums\Country;
use App\Enums\UserStatus;
use App\Filament\Resources\CandidateResource\Pages;
use App\Filament\Resources\CandidateResource\RelationManagers;
use App\Models\Candidate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('First name'),
                Forms\Components\TextInput::make('Last name'),
                Forms\Components\TextInput::make('Slug'),
                Forms\Components\Select::make('Country')
                    ->options(Country::getOptions())
                    ->searchable(),
                Forms\Components\TextInput::make('Address'),
                Forms\Components\TextInput::make('Phone'),
                Forms\Components\TextInput::make('Cbu'),
                Forms\Components\TextInput::make('Cuil'),
                Forms\Components\DatePicker::make('Birth date'),
                Forms\Components\Select::make('Status')
                    ->options(UserStatus::getOptions()),
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
            'index' => Pages\ListCandidates::route('/'),
            'create' => Pages\CreateCandidate::route('/create'),
            'edit' => Pages\EditCandidate::route('/{record}/edit'),
        ];
    }
}

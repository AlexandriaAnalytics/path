<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ExaminerResource\Pages;
use App\Filament\Admin\Resources\ExaminerResource\RelationManagers;
use App\Models\Examiner;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class ExaminerResource extends Resource
{
    protected static ?string $model = Examiner::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('surname')
                    ->required()
                    ->maxLength(255),
                    
                TextInput::make('phone')
                    ->required(),

                TextInput::make('address')
                    ->required()
                    ->maxLength(255),


                TextInput::make('email')
                    ->required()
                    ->maxLength(255),

                TextInput::make('password')
                    ->password()
                    ->required(fn ($livewire): bool => $livewire instanceof CreateRecord)
                    ->minLength(8)
                    ->same('passwordConfirmation')
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state)),

                TextInput::make('passwordConfirmation')
                    ->password()
                    ->label('Pasword confirmation')
                    ->required(fn ($livewire): bool => $livewire instanceof CreateRecord)
                    ->minLength(8)
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                ->sortable(),
                TextColumn::make('surname')
                ->sortable(),
                TextColumn::make('address')
                ->sortable(),
                TextColumn::make('phone')
                ->sortable(),
                TextColumn::make('email')
                ->sortable(),
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
            ])
            ->defaultSort('created_at','desc');
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
            'index' => Pages\ListExaminers::route('/'),
            'create' => Pages\CreateExaminer::route('/create'),
            'edit' => Pages\EditExaminer::route('/{record}/edit'),
        ];
    }
}

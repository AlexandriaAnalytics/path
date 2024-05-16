<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StatusActivityResource\Pages;
use App\Filament\Admin\Resources\StatusActivityResource\RelationManagers;
use App\Models\StatusActivity;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;

class StatusActivityResource extends Resource
{
    protected static ?string $model = StatusActivity::class;

    //protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Training settings';
    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Status';

    protected static ?string $pluralModelLabel = 'Status';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true),
                ColorPicker::make('color')
                    ->required(),
                Checkbox::make('default')
                    ->label('Default status')
                    ->disabled(function () {
                        return StatusActivity::where('default', 1)->exists();
                    })
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                ColorColumn::make('color')
                    ->searchable(),
                TextColumn::make('default')
                    ->formatStateUsing(function ($state) {
                        if ($state) {
                            return 'True';
                        } else {
                            return 'False';
                        }
                    })
                    ->badge()
                    ->color(function ($state) {
                        return $state ? 'success' : 'gray';
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created on')
                    ->sortable(),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
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
            'index' => Pages\ListStatusActivities::route('/'),
            'create' => Pages\CreateStatusActivity::route('/create'),
            'edit' => Pages\EditStatusActivity::route('/{record}/edit'),
        ];
    }
}

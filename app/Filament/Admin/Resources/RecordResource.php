<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RecordResource\Pages;
use App\Filament\Admin\Resources\RecordResource\RelationManagers;
use App\Models\Level;
use App\Models\Record;
use App\Models\Section;
use App\Models\StatusActivity;
use App\Models\Trainee;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecordResource extends Resource
{
    protected static ?string $model = Record::class;

    protected static ?string $navigationGroup = 'Training';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('trainee_id')
                    ->label('Trainee')
                    ->options(Trainee::all()->pluck('full_name', 'id'))
                    ->required(),
                Select::make('section_id')
                    ->label('Section')
                    ->options(Level::all()->pluck('name', 'id'))
                    ->required(),
                Select::make('status_id')
                    ->label('Status')
                    ->options(StatusActivity::all()->pluck('name', 'id'))
                    ->required(),
                TextInput::make('comments')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('trainee.full_name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('section_id')
                    ->label('Section')
                    ->formatStateUsing(function ($state) {
                        return Level::find($state)->name;
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                ColorColumn::make('status.color')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('comments')
                    ->sortable()
                    ->searchable()
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: false),
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
            'index' => Pages\ListRecords::route('/'),
            'create' => Pages\CreateRecord::route('/create'),
            'edit' => Pages\EditRecord::route('/{record}/edit'),
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

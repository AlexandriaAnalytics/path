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
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group as GroupingGroup;
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
                TextInput::make('trainee_id')
                    ->label('Trainee'),
                Select::make('section_id')
                    ->label('Section')
                    ->options(Section::all()->pluck('name', 'id'))
                    ->required(),
                Select::make('status_activity_id')
                    ->label('Status')
                    ->options(StatusActivity::all()->pluck('name', 'id'))
                    ->required(),
                TextInput::make('comments')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                GroupingGroup::make('trainee.user.name')
                    ->groupQueryUsing(fn (Builder $query) => $query->groupBy('trainee.full_name'))
                    ->collapsible(),
            ])
            ->defaultGroup('trainee.user.name')
            ->columns([
                TextColumn::make('trainee.user.name')
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
                TextColumn::make('result')
                    ->default('-')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                SelectColumn::make('status_activity_id')
                    ->options(StatusActivity::all()->pluck('name', 'id'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('comments')
                    ->sortable()
                    ->searchable()
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('updated_at')
                    ->label('Updated on')
                    ->sortable(),
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
            ])
            ->defaultSort('created_at', 'desc');
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

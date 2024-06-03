<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ChangeResource\Pages;
use App\Filament\Admin\Resources\ChangeResource\RelationManagers;
use App\Models\Candidate;
use App\Models\Change;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ChangeResource extends Resource
{
    protected static ?string $model = Change::class;

    protected static ?string $modelLabel = 'Change requests';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('candidate')
                    ->formatStateUsing(function ($record) {
                        return $record->candidate->student->name . ' ' . $record->candidate->student->surname;
                    }),
                TextInput::make('user')
                    ->label('Requested by')
                    ->formatStateUsing(function ($record) {
                        return $record->user->name . ' / ' . $record->candidate->student->institute->name;
                    }),
                TextInput::make('description')
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('candidate')
                    ->formatStateUsing(function ($record) {
                        return $record->candidate->student->name . ' ' . $record->candidate->student->surname . '/' . $record->candidate->student->institute->name;
                    }),
                TextColumn::make('user')
                    ->label('Requested by')
                    ->formatStateUsing(function ($record) {
                        return $record->user->name;
                    }),
                TextColumn::make('description')
                    ->wrap(),
                TextColumn::make('status')
                    ->formatStateUsing(function ($record) {
                        if ($record->status == 0) {
                            return 'Pending';
                        } else {
                            return 'Resolved';
                        }
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        0 => 'Pending',
                        1 => 'Resolved',
                    ]),
            ])
            ->actions([
                Action::make('mark as resolved')
                    ->icon('heroicon-o-check')
                    ->action(function (array $data, Change $change) {
                        $change->status = 1;
                        $change->save();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([]);
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
            'index' => Pages\ListChanges::route('/'),
            'create' => Pages\CreateChange::route('/create'),
            'view' => Pages\ViewChange::route('/{record}/view'),
        ];
    }
}

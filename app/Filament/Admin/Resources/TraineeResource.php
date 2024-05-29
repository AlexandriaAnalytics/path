<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TraineeResource\Pages;
use App\Filament\Admin\Resources\TraineeResource\RelationManagers;
use App\Models\Country;
use App\Models\Level;
use App\Models\Section;
use App\Models\Trainee;
use App\Models\TypeOfTraining;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Support\Facades\Hash;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class TraineeResource extends Resource
{
    protected static ?string $model = Trainee::class;
    protected static ?string $navigationGroup = 'Training';
    protected static ?string $navigationIcon = 'heroicon-m-users';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Personal information')
                        ->schema([
                            Select::make('user_id')
                                ->label('User')
                                ->options(User::all()->pluck('name', 'id'))
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->maxLength(255)
                                        ->autofocus(),
                                    Forms\Components\TextInput::make('email')
                                        ->email()
                                        ->required()
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('password')
                                        ->password()
                                        ->revealable()
                                        ->maxLength(255)
                                        ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                                        ->dehydrated(fn (?string $state): bool => filled($state))
                                        ->required(fn (string $operation): bool => $operation === 'create')
                                        ->hiddenOn(['view']),
                                ])
                                ->createOptionUsing(function (array $data): int {
                                    $existingUser = User::where('email', $data['email'])->first();

                                    if ($existingUser) {
                                        return $existingUser->id;
                                    } else {
                                        $user = \App\Models\User::create([
                                            'name' => $data['name'],
                                            'email' => $data['email'],
                                            'password' => $data['password']
                                        ]);
                                        return $user->id;
                                    }
                                })
                                ->columnSpan(4),
                            PhoneInput::make('phone')
                                ->required()
                                ->columnSpan(4),
                            Select::make('type_of_training_id')
                                ->label('Type of trainee')
                                ->options(TypeOfTraining::all()->pluck('name', 'id'))
                                ->required()
                                ->columnSpan(8),
                            Select::make('country_id')
                                ->label('Country')
                                ->options(Country::all()->pluck('name', 'id'))
                                ->required()
                                ->columnSpan(8),
                            TextInput::make('province_or_state')
                                ->required()
                                ->columnSpan(8),
                            TextInput::make('city')
                                ->required()
                                ->columnSpan(8),
                            TextInput::make('postcode')
                                ->required()
                                ->columnSpan(8),
                            TextInput::make('street_name')
                                ->required()
                                ->columnSpan(8),
                            TextInput::make('street_number')
                                ->numeric()
                                ->required()
                                ->columnSpan(8),
                            Toggle::make('status')
                                ->label('Active')
                                ->inline(false)
                                ->default(true)
                                ->required()
                                ->columnSpan(1),
                            TextInput::make('files')
                                ->columnSpan(7)
                        ])->columns(8),
                    Step::make('Sections')
                        ->schema([
                            Select::make('sections')
                                ->options(Section::all()->pluck('name', 'id'))
                                ->multiple()
                        ])
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Full name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('phone')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('country.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('province_or_state')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('city')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('postcode')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('street_name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('street_number')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('sections')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        $sections = [];
                        foreach (explode(", ", $state) as $section) {
                            array_push($sections, Level::whereId($section)->pluck('name')->first());
                        }
                        return implode(", ", $sections);
                    })
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('status')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'inactive' => 'danger',
                        'active' => 'success'
                    }),
                TextColumn::make('files')
                    ->sortable()
                    ->searchable()
                    ->url(fn (Trainee $record) => $record->files, shouldOpenInNewTab: true)
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
                    BulkAction::make('change_status')
                        ->icon('heroicon-o-arrows-right-left')
                        ->form([
                            Toggle::make('status')
                                ->label('Active')
                                ->inline(false)
                                ->default(true)
                                ->required()
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each->update([
                                'status' => $data['status'] ? 'active' : 'inactive',
                            ]);

                            Notification::make()
                                ->title('Trainee status updated successfully')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListTrainees::route('/'),
            'create' => Pages\CreateTrainee::route('/create'),
            'edit' => Pages\EditTrainee::route('/{record}/edit'),
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

<?php

namespace App\Filament\Admin\Resources;

use App\Exports\InstituteByIdExport;
use App\Filament\Admin\Resources\InstituteResource\Pages;
use App\Filament\Admin\Resources\InstituteResource\RelationManagers;
use App\Models\Candidate;
use App\Models\Country;
use App\Models\Institute;
use Doctrine\DBAL\Schema\Column;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\Column as ColumnsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;


class InstituteResource extends Resource
{
    protected static ?string $model = Institute::class;

    protected static ?string $modelLabel = 'Members and centres';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $navigationGroup = 'Corporate';

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Fieldset::make('Information')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->helperText('If omitted, the name will be generated from the first user added to the institution.')
                            ->label('Name of institution')
                            ->maxLength(255),

                        Select::make('institute_type_id')
                            ->label('Membership')
                            ->relationship('instituteType', 'name')
                            ->required()
                            ->native(false),
                        Select::make('owner_id')
                            ->required()
                            ->label('Head')
                            ->relationship('owner', 'name')
                            ->placeholder('Select a user')
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Full name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('password')
                                    ->password()
                                    ->revealable()
                                    ->required()
                                    ->maxLength(255)
                                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                                    ->dehydrated(fn (?string $state): bool => filled($state)),
                            ]),
                        TextInput::make('unique_number')
                            ->label('Unique ID')
                            ->hiddenOn('create'),
                        Fieldset::make('Contact Information')
                            ->schema([
                                PhoneInput::make('phone'),
                                TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Fieldset::make('Address')
                            ->columnSpan(2)
                            ->columns(3)
                            ->schema([
                                TextInput::make('street_name')
                                    ->required(),
                                TextInput::make('number')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1),
                                TextInput::make('city')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('postcode')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('province')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('country')
                                    ->required()
                                    ->options(Country::all()->pluck('name', 'id'))
                                    ->preload()
                                    ->searchable()
                            ])
                    ]),
                Fieldset::make('Administration')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('files_url')
                            ->label('Specific files URL')
                            ->type('url'),

                        Toggle::make('can_add_candidates')
                            ->label('Can register candidates')
                            ->default(false)
                            ->helperText('If enabled, the institution will be able to register candidates.'),

                        Toggle::make('can_view_registration_fee')
                            ->helperText('If enabled and 30 candidates or more are registered, the institution will be able to see the exam fee and the registration fee separately')
                            ->default(false),

                        Toggle::make('installment_plans')
                            ->helperText('If enabled, the institution will be able to offer subscription payments')
                            ->default(false)
                            ->label('Installment plans'),

                            Toggle::make('internal_payment_administration')
                            ->helperText('If enabled, the candidates of this institution can pay our exams in installments')
                            ->default(false)
                            ->label('Internal Payment Administration'),

                           
                            
                    ]),
                Fieldset::make('Exams and payments')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('maximum_cumulative_discount')
                            ->label('Maximum scholarship discount')
                            ->suffix('%')
                            ->type('number')
                            ->default(0)
                            ->minValue(0),
                            TextInput::make('mora')
                                    ->label('Late payment fee')
                                    ->numeric()
                                    ->default(0)
                                    ->postfix('%'),
                            Grid::make('late_paymet_fee')
                            ->schema([
                                TextInput::make('expiration_day_inferior')
                                ->label('late payment fee inferior (init day)')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(31),
                                TextInput::make('expiration_day_superior')
                                ->label('late payment fee superior (last day)')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(31)
                            ])->columns(2)
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('unique_number')
                    ->searchable()
                    ->label('Unique ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Institution')
                    ->sortable()
                    ->placeholder('(unnamed)')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('instituteType.name')
                    ->label('Membership')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('owner.name')
                    ->url(fn (Institute $institute) => route('filament.admin.resources.users.view', $institute->owner->id))
                    ->placeholder('(no owner)')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('email')
                    ->url(fn ($record) => 'mailto:' . $record->email, shouldOpenInNewTab: true)
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                PhoneColumn::make('phone')->displayFormat(PhoneInputNumberType::NATIONAL)
                    ->url(fn ($record) => 'https://api.whatsapp.com/send?phone=' . preg_replace("/[^\d]/", "", $record->phone), shouldOpenInNewTab: true)
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('files_url')
                    ->label('Files URL')
                    ->url(fn (Institute $record) => $record->files_url, shouldOpenInNewTab: true)
                    ->wrap()
                    ->placeholder('(no url)')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('students')
                    ->formatStateUsing(function (Institute $record) {
                        return $record->students->count();
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('candidates')
                    ->formatStateUsing(function (Institute $record) {
                        $students = $record->students;
                        $candidates = 0;
                        foreach ($students as $student) {
                            if (Candidate::query()
                                ->where('student_id', $student->id)->exists()
                            ) {
                                $candidates++;
                            }
                        }
                        return $candidates;
                    })
                    ->default(0)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')->label('Created on')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')->label('Updated on')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('deleted_at')->label('Deleted on')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->native(false),
                SelectFilter::make('country')
                    ->options(Country::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->multiple()
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    BulkAction::make('export-excel')
                        ->label('Download as Excel')
                        ->icon('heroicon-o-document')
                        ->action(fn (Collection $records) => (new InstituteByIdExport($records->pluck('id')))->download('members.xlsx')),
                    BulkAction::make('can_register_candidates')
                        ->icon('heroicon-o-user-group')
                        ->form([
                            Toggle::make('can_add_candidates')
                                ->label('Can register candidates')
                                ->default(false)
                        ])
                        ->action(function ($records) {
                            foreach ($records as $institute) {
                                $institute->can_add_candidates = 1;
                                $institute->save();
                            }
                        })
                ]),
            ])
            ->defaultSort('institutes.created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
            RelationManagers\StudentsRelationManager::class,
            RelationManagers\CandidatesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstitutes::route('/'),
            'create' => Pages\CreateInstitute::route('/create'),
            'view' => Pages\ViewInstitute::route('/{record}'),
            'edit' => Pages\EditInstitute::route('/{record}/edit'),
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

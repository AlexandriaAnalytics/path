<?php

namespace App\Filament\Admin\Resources;

use App\Exports\InstituteByIdExport;
use App\Filament\Admin\Resources\InstituteResource\Pages;
use App\Filament\Admin\Resources\InstituteResource\RelationManagers;
use App\Models\Institute;
use App\Models\InstituteLevel;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Support\Markdown;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Mpdf\Tag\FieldSet as TagFieldSet;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class InstituteResource extends Resource
{
    protected static ?string $model = Institute::class;

    protected static ?string $modelLabel = 'Members and Centres';

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
                            ->helperText('If omitted, the name will be generated from the first user added to the institute.')
                            ->label('Name of institution')
                            ->maxLength(255),

                        Select::make('institute_type_id')
                            ->relationship('instituteType', 'name')
                            ->required()
                            ->label('Type')
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
                                TextInput::make('country')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ]),
                Fieldset::make('Administration')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('files_url')
                            ->label('Institute files URL')
                            ->type('url')
                            ->hint('This institute\'s specific web folder.')
                            ->helperText('You can add global files in the settings.'),
                        Toggle::make('can_add_candidates')
                            ->default(true)
                            ->helperText('If enabled, the institute will be able to add candidates to exams.'),
                    ]),

                Fieldset::make('Exams and payements')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('instituteLevels')
                            ->grid(2)
                            ->relationship()
                            ->schema([
                                Select::make('level')
                                    ->relationship('level', 'name')
                                    ->disabled(),
                                TextInput::make('institute_diferencial_percentage_price')
                                    ->label('Percentage price difference')
                                    ->type('number')
                                    ->hint('Percentage difference in price for this institute.'),
                                TextInput::make('institute_diferencial_aditional_price')
                                    ->label('Additional price')
                                    ->type('number')
                                    ->hint('Additional price for this institute.'),
                                Toggle::make('can_edit')
                                    ->label('Can set right exam price') 
                                    ->reactive() // Ensure reactivity for conditional behavior
                                    ->hidden(fn (InstituteLevel $instituteLevel): bool => $instituteLevel->institute->instituteType->slug !== 'premium_exam_centre'),
                                
                            ]),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('(unnamed)')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('instituteType.name')
                    ->badge()
                    ->color(function (string $type) {
                        return match ($type) {
                            'Exam Centre' => 'green',
                            'Premium Exam Centre' => 'primary',
                            'Training Centre' => 'yellow',
                            'Premium Training Centre' => 'primary',
                            default => 'gray',
                        };
                    }),

                Tables\Columns\TextColumn::make('owner.name')
                    ->url(fn (Institute $institute) => route('filament.admin.resources.users.view', $institute->owner->id))
                    ->placeholder('(no owner)')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('email')
                    ->url(fn ($record) => 'mailto:' . $record->email, shouldOpenInNewTab: true)
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                PhoneColumn::make('phone')->displayFormat(PhoneInputNumberType::NATIONAL)
                    ->url(fn ($record) => 'https://api.whatsapp.com/send?phone=' . preg_replace("/[^\d]/", "", $record->phone), shouldOpenInNewTab: true)
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('files_url')
                    ->url(fn ($record) => $record->files_url, shouldOpenInNewTab: true)
                    ->wrap()
                    ->placeholder('(no url)')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('instituteType.name')
                    ->badge()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->label('Created on')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->label('Updated on')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')->label('Deleted on')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->native(false),
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
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
            RelationManagers\StudentsRelationManager::class,
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

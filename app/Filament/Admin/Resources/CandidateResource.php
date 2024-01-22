<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CandidateResource\Pages;
use App\Filament\Admin\Resources\CandidateResource\RelationManagers;
use App\Models\Candidate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
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
                Forms\Components\Select::make('exam_id')
                    ->relationship('exam', 'id')
                    ->required(),
                Forms\Components\Select::make('student_id')
                    ->relationship('student', 'id')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('full_name')
                            ->getStateUsing(fn (Candidate $record) => $record->student->first_name . ' ' . $record->student->last_name)
                            ->weight(FontWeight::Bold),
                        Tables\Columns\TextColumn::make('student.institute.name')
                            ->numeric(),
                    ]),
                    Tables\Columns\TextColumn::make('exam.session_name')
                        ->numeric(),
                ]),
                Tables\Columns\Layout\Panel::make([
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\TextColumn::make('id')
                            ->icon('heroicon-o-user')
                            ->label('Candidate ID')
                            ->description('Candidate ID')
                            ->sortable()
                            ->numeric(),
                        Tables\Columns\TextColumn::make('created_at')
                            ->icon('heroicon-o-clock')
                            ->description('Registered at')
                            ->date('Y-m-d H:i:s'),
                    ]),
                ])->collapsible(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('exam')
                    ->relationship('exam', 'session_name')
                    ->multiple()
                    ->native(false)
                    ->preload()
                    ->placeholder('All exams'),
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
            // 'create' => Pages\CreateCandidate::route('/create'),
            // 'view' => Pages\ViewCandidate::route('/{record}'),
            // 'edit' => Pages\EditCandidate::route('/{record}/edit'),
        ];
    }
}

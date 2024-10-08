<?php

namespace App\Filament\Management\Resources;

use App\Filament\Management\Resources\FinancingResource\Pages;
use App\Models\Candidate;
use App\Models\Financing;
use App\Models\Payment;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use PhpParser\Node\Stmt\Static_;

class FinancingResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $pluralModelLabel = 'Payments';
    protected static bool $hasTitleCaseModelLabel = false;

    public static function canViewAny(): bool
    {
        return Filament::getTenant()->internal_payment_administration || Filament::getTenant()->installment_plans;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->query(function () {
                return Payment::orderByDesc('created_at')->where('institute_id', Filament::getTenant()->id);
            })
            ->groups([
                Group::make('payment_id')
                    ->groupQueryUsing(fn (Builder $query) => $query->groupBy('payment_id'))
                    ->collapsible(),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('candidate_id')
                    ->label('Candidate')
                    ->formatStateUsing(function ($state) {
                        $candidate = Candidate::find($state);
                        if ($candidate) {
                            return $state . ' - ' . $candidate->student->name . ' ' . $candidate->student->surname;
                        } else {
                            return 'Withdrawn candidate';
                        }
                    }),

                Tables\Columns\TextColumn::make('currency')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('current_period')
                    ->date(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->prefix(fn (Financing $financing) => $financing->currency . '$ '),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'processing payment' => 'info'
                    }),
                Tables\Columns\TextColumn::make('link_to_ticket')
                    ->url(fn (Payment $record) => $record->link_to_ticket, shouldOpenInNewTab: true)
                    ->color('primary')
            ])
            ->filters([
                TernaryFilter::make('Withdraw candidates')
                    ->label('Withdraw candidates')
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                    ->queries(
                        true: fn (Builder $query) => $query->whereDoesntHave('candidate'),
                        false: fn (Builder $query) => $query->whereHas('candidate'),
                    )
                    ->native(false),
            ])
            ->actions([
                //
            ])
            ->actionsPosition(null)
            ->bulkActions([
                // 
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
            'index' => Pages\ListFinancings::route('/'),
            'create' => Pages\CreateFinancing::route('/create'),
        ];
    }
}

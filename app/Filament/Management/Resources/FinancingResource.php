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
use Filament\Tables\Table;
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
                return Payment::orderByDesc('created_at');
            })
            ->columns([
                Tables\Columns\TextColumn::make('candidate_id')
                    ->label('Candidate')
                    ->formatStateUsing(function ($state) {
                        $candidate = Candidate::find($state);
                        return $state . ' - ' . $candidate->student->name . ' ' . $candidate->student->surname;
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
                    ->action(fn (string $payment) => redirect()->to($payment))
                    ->color('primary')
            ])
            ->filters([
                //    
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

<?php

namespace App\Filament\Resources\CandidateResource\Widgets;

use App\Models\Candidate;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Mpdf\Tag\Columns;

class WidgetCandidatePaymentStatus extends BaseWidget
{
    protected static string $model = Candidate::class;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                
            )
            ->columns([
                Tables\Columns\TextColumn::make('id'),
            ]);
    }
}

<?php

namespace App\Filament\Candidate\Pages\Template;

use App\Filament\Candidate\Widgets\WidgetCandidate;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class CandidateDahboard extends Dashboard
{
    protected static ?int $navigationSort = -2;
    protected static string $view = 'filament.candidate.pages.template.candidate-dahboard';

    public static function canAccess(): bool
    {
        return isset(session('candidate')->candidate_number);
    }

    public function mount()
    {
        abort_unless(static::canAccess(), 403);
    }

    public function getHeading(): string|Htmlable
    {
        return 'Candidate Dashboard';
    }

    public function getWidgets(): array
    {
        return [
            WidgetCandidate::class,
        ];
    }

    
  protected function getActions(): array
{
    return [
        //Action::make('Print ticket')
        //    ->icon('heroicon-o-printer'),
        Action::make('Logout')
        ->color('danger')    
        ->url(route('candidate.logout')),
    ];
}


}

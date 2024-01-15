<?php

namespace App\Filament\Admin\Resources\ExamResource\Pages;

use App\Filament\Admin\Resources\ExamResource;
use App\Models\Candidate;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class ViewExam extends ViewRecord
{
    protected static string $resource = ExamResource::class;

    protected function getHeaderActions(): array
    {
        $refererUrl = Request::header('referer');
        $route = Route::getRoutes()->match(Request::create($refererUrl, 'GET'));
        $param = $route->parameter('tenant');

        $candidates = Candidate::where('institute_id', $param)->get();
        return [
            Actions\EditAction::make(),
            Actions\Action::make('assign_candidates')
                ->modalContent(view('candidates')->with('candidates', $candidates))
        ];
    }
}

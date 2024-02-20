<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    public $model = \App\Models\Candidate::class;

    public function confirm(Request $request)
    {
        $examId = $request->input('exam_id');
        $selectedCandidates = $request->input('selected_candidates');

        $existingEvaluations = $this->model::where('id_exam', $examId)->pluck('id_student')->toArray();

        $candidatesToDelete = array_diff($existingEvaluations, $selectedCandidates);
        $this->model::whereIn('id_student', $candidatesToDelete)->where('id_exam', $examId)->delete();

        foreach ($selectedCandidates as $candidateId) {
            $candidate = $this->model::updateOrCreate(
                ['id_student' => $candidateId, 'id_exam' => $examId]
            );
        }

        return redirect()->back();
    }

    public function show($id)
    {
        $candidate = Candidate::with(['student', 'exam'])->find($id);

        if (!$candidate) {
            abort(404, 'Candidate not found');
        }

        $data = [
            'candidate' => $candidate,
        ];

        return view('candidate-pdf', $data);
    }
}

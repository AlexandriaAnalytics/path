<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    public $model = \App\Models\Evaluation::class;

    public function confirm(Request $request)
    {
        $examId = $request->input('exam_id');
        $selectedCandidates = $request->input('candidates');

        // Obtener los IDs de evaluación existentes para el examen
        $existingEvaluations = $this->model::where('id_exam', $examId)->pluck('id_user')->toArray();

        // Borrar evaluaciones para los candidatos que ya no están presentes en la solicitud
        $candidatesToDelete = array_diff($existingEvaluations, $selectedCandidates);
        $this->model::whereIn('id_user', $candidatesToDelete)->where('id_exam', $examId)->delete();

        // Actualizar la tabla Evaluations para los candidatos seleccionados
        foreach ($selectedCandidates as $candidateId) {
            $evaluation = $this->model::updateOrCreate(
                ['id_user' => $candidateId, 'id_exam' => $examId],
                ['id_status' => 1]
            );
        }

        return redirect()->back();
    }
}

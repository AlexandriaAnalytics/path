<?php

namespace App\Livewire;

use Livewire\Component;

class LoginCandidate extends Component
{
  

    public $candidate_number;

    public function handleLoginCandidate()
    {
        if($this->candidate_number == null){
            session()->flash('error', 'Please enter your candidate number');
            return;
        }
        $candidate = \App\Models\Candidate::where('candidate_number', $this->candidate_number)->first();
        if(!$candidate){
            session()->flash('error', 'Candidate not found');
            return;
        }
        // add $candidate to session
        session(['candidate' => $candidate]);
        return redirect()->route('filament.candidate.pages.candidate-dahboard');
    }

    public function render()
    {
       
        return view('livewire.login-candidate');
    }
}

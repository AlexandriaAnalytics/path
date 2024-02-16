<?php

namespace App\Livewire;

use App\Models\Candidate;
use Livewire\Component;

class LoginCandidate extends Component
{
  

    public $id;

    public function handleLoginCandidate()
    {
        if($this->id == null){
            session()->flash('error', 'Please enter your candidate number');
            return;
        }

        $data = Candidate::find($this->id);
        if($data == null || !$data){
            session()->flash('error', 'Candidate not found');
            return;
        }
        $candidate = $data;
        // add $candidate to session
        session(['candidate' => $candidate]);
        return redirect()->route('filament.candidate.pages.candidate-dahboard');
    }

    public function render()
    {
       
        return view('livewire.login-candidate');
    }
}

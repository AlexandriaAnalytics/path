<?php

namespace App\Livewire;

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
        $candidate = \App\Models\Candidate::find($this->id)->first();
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebController extends Controller
{
    public function index(){
        if(auth()->guest()) return redirect()->route('filament.management.auth.login');
    }
}

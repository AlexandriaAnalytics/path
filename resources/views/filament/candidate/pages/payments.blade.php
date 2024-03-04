<x-filament-panels::page>
    @if (session('error'))
        <div class="text-red-600 text-center">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div>
        @if($candidate->status == 'paid')
            <h2>Exam Paid</h2>
        @elseif ($candidate->status == 'processing payment')
            <h2>Payment in process</h2>

        @elseif ($candidate->status == 'paying')
            <div>
                <header>
                    <h2>Payment State</h2>
                    <p>now you ar financing the exam </p>
                </header>
                <section>
                    <p>Instalments: <span>{{$candidate->instalment_counter}}</span></p>
                    <p>amount per coute: <span>{{$candidate->currency. '$ ' .$candidate->payments->first()->amount}}</span></p>
                    
                </section>


            </div>
        @else 
        <div style="display: grid">
            <x-filament-panels::form wire:submit="selectPaymentMethod">
            <h2 class="">Total amount: {{ $this->monetariUnitSymbol . ' ' . $this->total_amount }}</h2>
            <section>
                <h3>Exam session: <span>{{$examDate}}</span></h3>
            </section>            
            <blockquote>
                <h3>Details</h3>

                @foreach ($this->modules as $module)  
                    <ul>
                        <li>{{$module['name']}} - {{$this->candidate->getMonetaryString()}} {{$module['price']}}</li>
                    </ul>
                @endforeach
            </blockquote>
    
    
            {{ $this->form }}
    
            <style>
                button.submit {
                    border: 1px solid black;
                    padding: 1rem;
                    background-color: #044968;
                    border-radius: 10px;
                    font-weight: bold;
                    color: white;
                    cursor: pointer;
                }
    
                button.submit:hover {
                    background-color: #6c8ebf;
                }
            </style>
            <button class="submit" type="submit">
                Process payment
            </button>
        </x-filament-panels::form>
        </div>
        
        @endif
    </div>



   
    </x-filament-panels::page>

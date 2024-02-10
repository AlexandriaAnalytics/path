<x-filament-panels::page>
    @if (session('error'))
        <div class="text-red-600 text-center">
            <p>{{ session('error') }}</p>
        </div>
    @endif
    <x-filament-panels::form wire:submit="selectPaymentMethod">
        <h2 class="">Total Amount: {{ $this->monetariUnitSymbol . ' ' . $this->total_amount }}</h2>
        
        <blockquote>
            <h3>Detail</h3>
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
                background-color: #92b8fe;
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
            process payment
        </button>
    </x-filament-panels::form>
</x-filament-panels::page>
